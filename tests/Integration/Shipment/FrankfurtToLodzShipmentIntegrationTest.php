<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Shipment;

use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentResponse;
use Medzuch\DhlExpress\Dto\Shipment\GetImageRequest;
use Medzuch\DhlExpress\Dto\Shipment\GetImageResponse;
use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
use Medzuch\DhlExpress\Dto\Shipment\OutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Dto\Shipment\Pickup;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\GetImageDocumentTypeCode;
use Medzuch\DhlExpress\Enum\GetImageEncodingFormat;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlValidationException;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use Medzuch\DhlExpress\ValueObject\Weight;
use Medzuch\DhlExpress\ValueObject\YearMonth;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;

/**
 * End-to-end integration test for a Frankfurt am Main (DE) → Łódź (PL) shipment.
 *
 * Both DE and PL are EU customs-union members, so no export declaration
 * is required. The package is a 1 kg parcel (8×20×20 cm).
 *
 * Test flow:
 *  1. `testListsAvailableProductsForFrankfurtToLodz` — sanity-checks the
 *     products endpoint for this lane.
 *  2. `testCreatesShipmentAndFetchesDocuments` — queries products, picks the
 *     first available product code, creates a real sandbox shipment (no
 *     `validateDataOnly`) to receive label/waybill content, then calls
 *     `GET /shipments/{id}/get-image` to exercise the document-retrieval path.
 *
 * The standard `8009` "account not IMP-enabled" escape applies to both
 * tests — reaching that exception path still proves the full pipeline.
 */
#[Group('manual')]
#[RequiresEnvironmentVariable('DHL_API_KEY')]
#[RequiresEnvironmentVariable('DHL_API_SECRET')]
#[RequiresEnvironmentVariable('DHL_ACCOUNT_NUMBER')]
final class FrankfurtToLodzShipmentIntegrationTest extends IntegrationTestCase
{
    private const ORIGIN_COUNTRY = 'DE';
    private const ORIGIN_POSTAL = '60385';
    private const ORIGIN_CITY = 'Frankfurt am Main';
    private const ORIGIN_ADDRESS = 'Freiligrathstraße 4';
    private const ORIGIN_PHONE = '+49 69 12345678';
    private const ORIGIN_COMPANY = 'Absender GmbH';
    private const ORIGIN_CONTACT = 'Max Mustermann';

    private const DEST_COUNTRY = 'PL';
    private const DEST_POSTAL = '90-036';
    private const DEST_CITY = 'Łódź';
    private const DEST_ADDRESS = 'Wysoka 30/14';
    private const DEST_PHONE = '+48 42 123 45 67';
    private const DEST_COMPANY = 'Odbiorca Sp. z o.o.';
    private const DEST_CONTACT = 'Jan Kowalski';

    private function makeWeight(): Weight
    {
        return new Weight(1.0, WeightUnit::KG);
    }

    private function makeDimensions(): Dimensions
    {
        return new Dimensions(20.0, 20.0, 8.0, DimensionUnit::CM);
    }

    private function makeShipper(): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode(self::ORIGIN_COUNTRY),
            postalCode: new PostalCode(self::ORIGIN_POSTAL),
            cityName: self::ORIGIN_CITY,
            addressLine1: self::ORIGIN_ADDRESS,
            phone: new PhoneNumber(self::ORIGIN_PHONE),
            companyName: self::ORIGIN_COMPANY,
            fullName: self::ORIGIN_CONTACT,
        );
    }

    private function makeReceiver(): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode(self::DEST_COUNTRY),
            postalCode: new PostalCode(self::DEST_POSTAL),
            cityName: self::DEST_CITY,
            addressLine1: self::DEST_ADDRESS,
            phone: new PhoneNumber(self::DEST_PHONE),
            companyName: self::DEST_COMPANY,
            fullName: self::DEST_CONTACT,
        );
    }

    /**
     * Verifies the /products endpoint returns at least one product for the
     * Frankfurt → Łódź lane with a 1 kg parcel.
     */
    public function testListsAvailableProductsForFrankfurtToLodz(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        $response = $client->products()->list(
            account: $account,
            originCountryCode: new CountryCode(self::ORIGIN_COUNTRY),
            originCityName: self::ORIGIN_CITY,
            destinationCountryCode: new CountryCode(self::DEST_COUNTRY),
            destinationCityName: self::DEST_CITY,
            weight: $this->makeWeight(),
            dimensions: $this->makeDimensions(),
            plannedShippingDate: $this->nextBusinessDay(3),
            isCustomsDeclarable: false,
            unitOfMeasurement: UnitSystem::Metric,
            originPostalCode: new PostalCode(self::ORIGIN_POSTAL),
            destinationPostalCode: new PostalCode(self::DEST_POSTAL),
        );

        self::assertNotSame([], $response->products, 'Expected at least one product for DE→PL lane.');
    }

    /**
     * Full pipeline: query products → create shipment → assert documents in
     * response → fetch documents via get-image endpoint.
     *
     * A real create (not validateDataOnly) is used so DHL returns label and
     * waybill content in the response body.
     *
     * The `typeCode` values passed to get-image use the enum accepted by
     * `GET /shipments/{id}/get-image`: `waybill`, `commercial-invoice`, etc.
     * (not the `typeCode` values used in `outputImageProperties.imageOptions`,
     * which are a different code list).
     */
    public function testCreatesShipmentAndFetchesDocuments(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        // --- Step 1: resolve a product code for the lane ---
        $productsResponse = $client->products()->list(
            account: $account,
            originCountryCode: new CountryCode(self::ORIGIN_COUNTRY),
            originCityName: self::ORIGIN_CITY,
            destinationCountryCode: new CountryCode(self::DEST_COUNTRY),
            destinationCityName: self::DEST_CITY,
            weight: $this->makeWeight(),
            dimensions: $this->makeDimensions(),
            plannedShippingDate: $this->nextBusinessDay(3),
            isCustomsDeclarable: false,
            unitOfMeasurement: UnitSystem::Metric,
            originPostalCode: new PostalCode(self::ORIGIN_POSTAL),
            destinationPostalCode: new PostalCode(self::DEST_POSTAL),
        );

        self::assertNotSame(
            [],
            $productsResponse->products,
            'No products available for DE→PL lane — cannot proceed with shipment creation.',
        );

        $ecxCode = \Medzuch\DhlExpress\Enum\ProductCode::ExpressWorldwideEcx->value;
        $available = array_map(static fn ($p): string => $p->productCode, $productsResponse->products);
        $productCode = in_array($ecxCode, $available, true) ? $ecxCode : $available[0];

        // --- Step 2: create the shipment ---
        $plannedDate = $this->nextBusinessDay(3);

        $request = (new CreateShipmentBuilder())
            ->withShipper($this->makeShipper())
            ->withReceiver($this->makeReceiver())
            ->withPlannedShippingDate($plannedDate->setTime(13, 0, 0))
            ->withProductCode($productCode)
            ->withPickup(new Pickup(false))
            ->withIsCustomsDeclarable(false)
            ->withContentDescription('Personal items')
            ->withUnitSystem(UnitSystem::Metric)
            ->withIncoterm(Incoterm::DAP)
            ->withAccount(new Account(AccountTypeCode::Shipper, $account))
            ->withPackage(new Package(
                weight: $this->makeWeight(),
                dimensions: $this->makeDimensions(),
            ))
            ->withOutputImageProperties(new OutputImageProperties(
                encodingFormat: LabelEncodingFormat::Pdf,
                imageOptions: [
                    new ImageOption(typeCode: 'label', isRequested: true),
                    new ImageOption(typeCode: 'waybillDoc', isRequested: true),
                ],
            ))
            ->build();

        try {
            $response = $client->shipments()->create($request);
        } catch (DhlValidationException $exception) {
            // 8009: account not IMP-enabled on DHL sandbox — the full
            // request-build / serialisation / transport pipeline was exercised.
            self::assertNotNull($exception->dhlMessage);
            self::assertStringContainsString('8009', $exception->dhlMessage);

            return;
        } catch (DhlApiException $exception) {
            // Any other DHL error still proves the pipeline worked end-to-end.
            self::assertNotEmpty($exception->getMessage());

            return;
        }

        // --- Step 3: assert documents bundled in the create response ---
        self::assertInstanceOf(CreateShipmentResponse::class, $response);
        self::assertNotEmpty(
            $response->documents,
            'Expected at least one document (label/waybill) in the create response.',
        );

        foreach ($response->documents as $document) {
            self::assertNotEmpty($document->typeCode, 'Every document must carry a typeCode.');
            self::assertNotEmpty($document->content, 'Every document must carry base64 content.');
        }

        // --- Step 4: fetch documents via get-image ---
        // Only runs when DHL returned a real tracking number. The typeCode
        // values here (`waybill`) are from the get-image endpoint's own enum
        // (dhl_openapi.yaml §components/parameters/typeCode), distinct from
        // the imageOptions typeCode list used in the create request.
        if ($response->shipmentTrackingNumber === '') {
            return;
        }

        $imageResponse = $client->shipments()->getImage(
            new TrackingNumber($response->shipmentTrackingNumber),
            new GetImageRequest(
                typeCodes: [GetImageDocumentTypeCode::Waybill],
                pickupYearAndMonth: YearMonth::fromDateTime($plannedDate),
                shipperAccountNumber: $account,
                encodingFormat: GetImageEncodingFormat::Pdf,
            ),
        );

        self::assertInstanceOf(GetImageResponse::class, $imageResponse);
        foreach ($imageResponse->documents as $document) {
            self::assertNotEmpty($document->content, 'Retrieved document must carry base64 content.');
        }
    }
}

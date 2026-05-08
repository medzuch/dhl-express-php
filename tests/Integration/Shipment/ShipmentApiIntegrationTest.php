<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Shipment;

use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentResponse;
use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
use Medzuch\DhlExpress\Dto\Shipment\OutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\DhlValidationException;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;

/**
 * Sandbox check for `POST /shipments` (Phase 4a happy-path).
 *
 * Uses `validateDataOnly=true` so the request exercises the full
 * request-build / transport / response-hydrate pipeline against the
 * real DHL sandbox without producing a real shipment. That keeps the
 * suite re-runnable without polluting the sandbox account with
 * accumulated test shipments.
 *
 * Domestic CZ→CZ lane (Prague→Brno) — no customs needed, the simplest
 * shape DHL accepts.
 *
 * Many DHL sandbox accounts are not IMP-enabled for shipment booking
 * and surface error code `8009` ("Shipper Account ... not IMP enabled
 * and cannot be used as Freight Payer account") when calling
 * `POST /shipments` even in validation-only mode. Reaching that
 * exception path still proves the full request/response pipeline
 * works, so we treat it as a successful exercise of the API just like
 * `IdentifierApiIntegrationTest` does for breakbulk-authorization.
 */
#[Group('integration')]
#[RequiresEnvironmentVariable('DHL_API_KEY')]
#[RequiresEnvironmentVariable('DHL_API_SECRET')]
#[RequiresEnvironmentVariable('DHL_ACCOUNT_NUMBER')]
final class ShipmentApiIntegrationTest extends IntegrationTestCase
{
    public function testValidatesDomesticCzShipmentInValidationMode(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        $request = (new CreateShipmentBuilder())
            ->withShipper(new ContactAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('14800'),
                cityName: 'Prague',
                addressLine1: 'Vaclavske namesti 1',
                phone: new PhoneNumber('+420 222 333 444'),
                companyName: 'Medzuch s.r.o.',
                fullName: 'Marcin Mech',
            ))
            ->withReceiver(new ContactAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('60200'),
                cityName: 'Brno',
                addressLine1: 'Namesti Svobody 10',
                phone: new PhoneNumber('+420 555 666 777'),
                companyName: 'Receiver s.r.o.',
                fullName: 'Receiver Name',
            ))
            ->withPlannedShippingDate($this->nextBusinessDay(3)->setTime(13, 0, 0))
            ->withProductCode('N')
            ->withPickupRequested(false)
            ->withIsCustomsDeclarable(false)
            ->withContentDescription('Books')
            ->withUnitSystem(UnitSystem::Metric)
            ->withAccount(new Account(AccountTypeCode::Shipper, $account))
            ->withPackage(new Package(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
            ))
            ->withOutputImageProperties(new OutputImageProperties(
                encodingFormat: LabelEncodingFormat::Pdf,
                imageOptions: [
                    new ImageOption(typeCode: 'label', isRequested: true),
                ],
            ))
            ->build();

        try {
            $response = $client->shipments()->create($request, validateDataOnly: true);
        } catch (DhlValidationException $exception) {
            // 8009: The Shipper Account is invalid. Account is not IMP
            // enabled and cannot be used as Freight Payer account for the
            // shipment booking. Common on DHL sandbox test accounts; the
            // exception path still proves the full pipeline works.
            self::assertNotNull($exception->dhlMessage);
            self::assertStringContainsString('8009', $exception->dhlMessage);

            return;
        }

        // Success path — only reachable when the sandbox account is
        // IMP-enabled. validateDataOnly returns the same response shape;
        // some fields (notably shipmentTrackingNumber) are typically
        // empty because no real shipment was created.
        self::assertInstanceOf(CreateShipmentResponse::class, $response);
    }
}

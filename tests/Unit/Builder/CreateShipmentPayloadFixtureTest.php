<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Builder;

use DateTimeImmutable;
use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\DangerousGoods;
use Medzuch\DhlExpress\Dto\Shipment\DeclarationNote;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportInvoice;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Dto\Shipment\ParentShipment;
use Medzuch\DhlExpress\Dto\Shipment\Pickup;
use Medzuch\DhlExpress\Dto\Shipment\ValueAddedService;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DangerousGoodsContentId;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\ExportReasonType;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\InvoiceFunction;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

/**
 * Golden-file test: a customs-declarable shipment using the MyDHL API
 * 3.4.0 request features must serialize exactly to
 * `tests/Fixtures/shipments/create-shipment-request-3.4.0.json`.
 *
 * The fixture was checked against
 * `supermodelIoLogisticsExpressCreateShipmentRequest` in
 * `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`; regenerate and re-validate
 * it whenever the payload shape changes on purpose.
 */
final class CreateShipmentPayloadFixtureTest extends TestCase
{
    public function testCustomsShipmentMatches340RequestFixture(): void
    {
        $payload = $this->buildRequest()->toArray();

        self::assertEquals($this->loadFixture(), $this->normalize($payload));
    }

    private function buildRequest(): \Medzuch\DhlExpress\Dto\Shipment\CreateShipmentRequest
    {
        return (new CreateShipmentBuilder())
            ->withShipper(new ContactAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('14800'),
                cityName: 'Prague',
                addressLine1: 'Vaclavske namesti 1',
                phone: new PhoneNumber('+420 222 333 444'),
                companyName: 'Alfa Trading s.r.o.',
                fullName: 'Jan Novak',
            ))
            ->withReceiver(new ContactAddress(
                countryCode: new CountryCode('US'),
                postalCode: new PostalCode('10001'),
                cityName: 'New York',
                addressLine1: '350 Fifth Avenue',
                phone: new PhoneNumber('+1 212 555 0100'),
                companyName: 'Receiver Inc.',
                fullName: 'John Smith',
            ))
            ->withPlannedShippingDate(new DateTimeImmutable('2026-11-02T13:00:00+01:00'))
            ->withProductCode('P')
            ->withPickup(new Pickup(false))
            ->withIsCustomsDeclarable(true)
            ->withContentDescription('Cotton T-shirts | dry ice')
            ->withUnitSystem(UnitSystem::Metric)
            ->withDimensionUnitSystem(UnitSystem::Imperial)
            ->withIncoterm(Incoterm::DAP)
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withPackage(new Package(
                weight: new Weight(2.5, WeightUnit::KG),
                dimensions: new Dimensions(10.0, 8.0, 6.0, DimensionUnit::IN),
            ))
            ->withValueAddedService(new ValueAddedService('HC'))
            ->withDangerousGoods(new DangerousGoods(
                contentId: DangerousGoodsContentId::DryIceUN1845,
                dryIceTotalNetWeight: 1.0,
                packagesCount: 1,
                dryIceUnitOfMeasurement: UnitSystem::Metric,
            ))
            ->withParentShipment(new ParentShipment(productCode: 'P', packagesCount: 2))
            ->addExportDeclaration(new ExportDeclaration(
                lineItems: [$this->lineItem(1, 'Cotton T-shirt', 20.00, 3)],
                invoice: new ExportInvoice(
                    number: 'INV-1',
                    date: new DateTimeImmutable('2026-11-02'),
                    function: InvoiceFunction::Export,
                    signatureName: 'Jane Doe',
                    signatureTitle: 'Export Manager',
                    totalNetWeight: 1.5,
                    totalGrossWeight: 1.8,
                    termsOfPayment: '30 days',
                ),
                exportReason: 'Sale of goods',
                declarationNotes: [new DeclarationNote('First shipment of the season')],
                packageMarks: 'Fragile',
            ))
            ->addExportDeclaration(new ExportDeclaration(
                lineItems: [$this->lineItem(1, 'Cotton socks', 10.00, 4)],
                invoice: new ExportInvoice(
                    number: 'INV-2',
                    date: new DateTimeImmutable('2026-11-02'),
                    function: InvoiceFunction::Export,
                    instructions: ['Keep dry'],
                    customerDataTextEntries: ['Order 4711'],
                ),
                incoterm: Incoterm::EXW,
                payerVATNumber: '12345ED',
            ))
            ->withDeclaredValue(100.00, 'EUR')
            ->build();
    }

    private function lineItem(int $number, string $description, float $price, int $quantity): ExportLineItem
    {
        return new ExportLineItem(
            number: $number,
            description: $description,
            price: $price,
            quantity: new LineItemQuantity($quantity, LineItemQuantityUnit::PCS),
            manufacturerCountry: 'CZ',
            weight: new LineItemWeight(netValue: 0.2, grossValue: 0.25),
            exportReasonType: ExportReasonType::Permanent,
            additionalInformation: ['100% cotton'],
        );
    }

    /**
     * @return array<mixed>
     */
    private function loadFixture(): array
    {
        $contents = file_get_contents(__DIR__ . '/../../Fixtures/shipments/create-shipment-request-3.4.0.json');
        if ($contents === false) {
            self::fail('Failed to load create-shipment-request-3.4.0.json');
        }
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        return $decoded;
    }

    /**
     * Round-trip through JSON so floats like 20.0 compare equal to the
     * fixture's integer `20`.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<mixed>
     */
    private function normalize(array $payload): array
    {
        $decoded = json_decode(json_encode($payload, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        return $decoded;
    }
}

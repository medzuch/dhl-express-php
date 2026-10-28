<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Invoice;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Invoice\UploadStandaloneInvoiceDataRequest;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportInvoice;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceImageOption;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceOutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\InvoiceFunction;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use PHPUnit\Framework\TestCase;

final class UploadStandaloneInvoiceDataRequestTest extends TestCase
{
    private function makeDeclaration(): ExportDeclaration
    {
        return new ExportDeclaration(
            invoice: new ExportInvoice(
                number: 'INV-001',
                date: new DateTimeImmutable('2026-05-01'),
                function: InvoiceFunction::Export,
            ),
            incoterm: Incoterm::DAP,
            lineItems: [
                new ExportLineItem(
                    number: 1,
                    description: 'Product',
                    price: 100.00,
                    quantity: new LineItemQuantity(1, LineItemQuantityUnit::PCS),
                    manufacturerCountry: 'CZ',
                    weight: new LineItemWeight(netValue: 1.0, grossValue: 1.2),
                ),
            ],
        );
    }

    public function testDeclarationWithoutIncotermIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exportDeclarations[1].incoterm');

        new UploadStandaloneInvoiceDataRequest(
            exportDeclarations: [
                $this->makeDeclaration(),
                new ExportDeclaration(
                    lineItems: $this->makeDeclaration()->lineItems,
                    invoice: $this->makeDeclaration()->invoice,
                ),
            ],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            shipmentTrackingNumber: new TrackingNumber('1234567890'),
        );
    }

    public function testDeclarationWithoutInvoiceFunctionIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exportDeclarations[0].invoice.function');

        new UploadStandaloneInvoiceDataRequest(
            exportDeclarations: [
                new ExportDeclaration(
                    lineItems: $this->makeDeclaration()->lineItems,
                    invoice: new ExportInvoice(number: 'INV-001', date: new DateTimeImmutable('2026-05-01')),
                    incoterm: Incoterm::DAP,
                ),
            ],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            shipmentTrackingNumber: new TrackingNumber('1234567890'),
        );
    }

    public function testCreateShipmentOnlyFieldIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exportDeclarations[0].packageMarks');

        new UploadStandaloneInvoiceDataRequest(
            exportDeclarations: [
                new ExportDeclaration(
                    lineItems: $this->makeDeclaration()->lineItems,
                    invoice: $this->makeDeclaration()->invoice,
                    incoterm: Incoterm::DAP,
                    packageMarks: 'Fragile',
                ),
            ],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            shipmentTrackingNumber: new TrackingNumber('1234567890'),
        );
    }

    public function testSerializedInvoiceCarriesFunction(): void
    {
        $request = new UploadStandaloneInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            shipmentTrackingNumber: new TrackingNumber('1234567890'),
        );

        self::assertSame('export', $request->toArray()['content']['exportDeclaration'][0]['invoice']['function']);
    }

    public function testEverySerializedDeclarationCarriesItsIncoterm(): void
    {
        $request = new UploadStandaloneInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration(), $this->makeDeclaration()->withIncoterm(Incoterm::DDP)],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            shipmentTrackingNumber: new TrackingNumber('1234567890'),
        );

        $declarations = $request->toArray()['content']['exportDeclaration'];

        self::assertSame('DAP', $declarations[0]['incoterm']);
        self::assertSame('DDP', $declarations[1]['incoterm']);
    }

    public function testToArrayProducesExpectedShape(): void
    {
        $request = new UploadStandaloneInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            shipmentTrackingNumber: new TrackingNumber('1234567890'),
        );

        $result = $request->toArray();

        self::assertSame('1234567890', $result['shipmentTrackingNumber']);
        self::assertArrayHasKey('content', $result);
        self::assertSame('EUR', $result['content']['currency']);
        self::assertSame('metric', $result['content']['unitOfMeasurement']);
    }

    public function testToArrayOmitsTrackingNumberWhenAbsent(): void
    {
        $request = new UploadStandaloneInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
        );

        $result = $request->toArray();

        self::assertArrayNotHasKey('shipmentTrackingNumber', $result);
        self::assertCount(1, $result['accounts']);
    }

    public function testToArrayIncludesAllOptionalSections(): void
    {
        $request = new UploadStandaloneInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'USD',
            unitOfMeasurement: UnitSystem::Imperial,
            shipmentTrackingNumber: new TrackingNumber('9999'),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('1'))],
            plannedShipDate: new DateTimeImmutable('2026-06-01'),
            outputImageProperties: new InvoiceOutputImageProperties(
                imageOptions: [new InvoiceImageOption(templateName: 'COMMERCIAL_INVOICE_P_10', isRequested: true)],
            ),
        );

        $result = $request->toArray();

        self::assertSame('9999', $result['shipmentTrackingNumber']);
        self::assertSame('2026-06-01', $result['plannedShipDate']);
        self::assertCount(1, $result['accounts']);
        self::assertSame('imperial', $result['content']['unitOfMeasurement']);
        self::assertArrayHasKey('outputImageProperties', $result);
    }
}

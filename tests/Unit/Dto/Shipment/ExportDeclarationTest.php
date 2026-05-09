<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Shipment\AdditionalCharge;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\Exporter;
use Medzuch\DhlExpress\Dto\Shipment\ExportInvoice;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\ExportRemark;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Enum\AdditionalChargeTypeCode;
use Medzuch\DhlExpress\Enum\ExportReasonType;
use Medzuch\DhlExpress\Enum\InvoiceFunction;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use Medzuch\DhlExpress\Enum\ShipmentPurpose;
use PHPUnit\Framework\TestCase;

final class ExportDeclarationTest extends TestCase
{
    private function makeLineItem(int $number = 1): ExportLineItem
    {
        return new ExportLineItem(
            number: $number,
            description: 'Test product',
            price: 100.00,
            quantity: new LineItemQuantity(5, LineItemQuantityUnit::PCS),
            manufacturerCountry: 'CZ',
            weight: new LineItemWeight(netValue: 1.0, grossValue: 1.2),
        );
    }

    public function testToArrayWithRequiredFieldOnly(): void
    {
        $declaration = new ExportDeclaration(lineItems: [$this->makeLineItem()]);

        $result = $declaration->toArray();

        self::assertCount(1, $result['lineItems']);
        self::assertSame(1, $result['lineItems'][0]['number']);
        self::assertSame('Test product', $result['lineItems'][0]['description']);
    }

    public function testToArrayWithAllOptionalFields(): void
    {
        $declaration = new ExportDeclaration(
            lineItems: [$this->makeLineItem(1), $this->makeLineItem(2)],
            invoice: new ExportInvoice(
                number: 'INV-001',
                date: new DateTimeImmutable('2026-05-01'),
                function: InvoiceFunction::Export,
            ),
            remarks: [new ExportRemark('Handle with care')],
            additionalCharges: [new AdditionalCharge(10.00, AdditionalChargeTypeCode::Freight)],
            placeOfIncoterm: 'Prague',
            recipientReference: 'REF-001',
            exporter: new Exporter(id: 'EXP-123'),
            exportReasonType: ExportReasonType::Commercial,
            shipmentType: ShipmentPurpose::Commercial,
        );

        $result = $declaration->toArray();

        self::assertCount(2, $result['lineItems']);
        self::assertSame('INV-001', $result['invoice']['number']);
        self::assertCount(1, $result['remarks']);
        self::assertSame('Handle with care', $result['remarks'][0]['value']);
        self::assertCount(1, $result['additionalCharges']);
        self::assertSame(10.00, $result['additionalCharges'][0]['value']);
        self::assertSame('freight', $result['additionalCharges'][0]['typeCode']);
        self::assertSame('Prague', $result['placeOfIncoterm']);
        self::assertSame('REF-001', $result['recipientReference']);
        self::assertSame('EXP-123', $result['exporter']['id']);
        self::assertSame('commercial_purpose_or_sale', $result['exportReasonType']);
        self::assertSame('commercial', $result['shipmentType']);
    }

    public function testToArrayOmitsNullOptionals(): void
    {
        $declaration = new ExportDeclaration(lineItems: [$this->makeLineItem()]);

        $result = $declaration->toArray();

        self::assertArrayNotHasKey('invoice', $result);
        self::assertArrayNotHasKey('remarks', $result);
        self::assertArrayNotHasKey('additionalCharges', $result);
        self::assertArrayNotHasKey('placeOfIncoterm', $result);
        self::assertArrayNotHasKey('recipientReference', $result);
        self::assertArrayNotHasKey('exporter', $result);
        self::assertArrayNotHasKey('exportReasonType', $result);
        self::assertArrayNotHasKey('shipmentType', $result);
        self::assertArrayNotHasKey('customsDocuments', $result);
    }
}

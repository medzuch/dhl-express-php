<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use InvalidArgumentException;
use Medzuch\DhlExpress\Dto\Shipment\AdditionalCharge;
use Medzuch\DhlExpress\Dto\Shipment\DeclarationNote;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\Exporter;
use Medzuch\DhlExpress\Dto\Shipment\ExportInvoice;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\ExportRemark;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Enum\AdditionalChargeTypeCode;
use Medzuch\DhlExpress\Enum\ExportReasonType;
use Medzuch\DhlExpress\Enum\Incoterm;
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

    private function makeInvoice(string $number = 'INV-001'): ExportInvoice
    {
        return new ExportInvoice(
            number: $number,
            date: new DateTimeImmutable('2026-05-01'),
            function: InvoiceFunction::Export,
        );
    }

    public function testToArrayWithRequiredFieldsOnly(): void
    {
        $declaration = new ExportDeclaration(lineItems: [$this->makeLineItem()], invoice: $this->makeInvoice());

        $result = $declaration->toArray();

        self::assertCount(1, $result['lineItems']);
        self::assertSame(1, $result['lineItems'][0]['number']);
        self::assertSame('Test product', $result['lineItems'][0]['description']);
        self::assertSame('INV-001', $result['invoice']['number']);
        self::assertArrayNotHasKey('incoterm', $result);
    }

    public function testCompleteDeclarationSerializesIncoterm(): void
    {
        $declaration = new ExportDeclaration(
            lineItems: [$this->makeLineItem()],
            invoice: $this->makeInvoice(),
            incoterm: Incoterm::DAP,
        );

        $result = $declaration->toArray();

        self::assertArrayHasKey('lineItems', $result);
        self::assertArrayHasKey('invoice', $result);
        self::assertSame('DAP', $result['incoterm']);
    }

    public function testWithIncotermReturnsCopyWithIncotermAndSameFields(): void
    {
        $original = new ExportDeclaration(
            lineItems: [$this->makeLineItem()],
            invoice: $this->makeInvoice(),
            placeOfIncoterm: 'Prague',
            exportReason: 'Sale',
        );

        $copy = $original->withIncoterm(Incoterm::DDP);

        self::assertNull($original->incoterm);
        self::assertSame(Incoterm::DDP, $copy->incoterm);
        self::assertEquals(
            array_merge($original->toArray(), ['incoterm' => 'DDP']),
            $copy->toArray(),
        );
    }

    public function testDescriptiveFieldsAreSerialized(): void
    {
        $declaration = new ExportDeclaration(
            lineItems: [$this->makeLineItem()],
            invoice: $this->makeInvoice(),
            exportReason: 'Sale of goods',
            declarationNotes: [new DeclarationNote('First note'), new DeclarationNote('Second note')],
            packageMarks: 'Fragile glass bottle',
            payerVATNumber: '12345ED',
        );

        $result = $declaration->toArray();

        self::assertSame('Sale of goods', $result['exportReason']);
        self::assertSame([['value' => 'First note'], ['value' => 'Second note']], $result['declarationNotes']);
        self::assertSame('Fragile glass bottle', $result['packageMarks']);
        self::assertSame('12345ED', $result['payerVATNumber']);
    }

    public function testExportReasonLongerThanThirtyCharactersIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exportReason');

        new ExportDeclaration(
            lineItems: [$this->makeLineItem()],
            invoice: $this->makeInvoice(),
            exportReason: str_repeat('a', 31),
        );
    }

    public function testMoreThanThreeDeclarationNotesAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('declarationNotes');

        new ExportDeclaration(
            lineItems: [$this->makeLineItem()],
            invoice: $this->makeInvoice(),
            declarationNotes: array_fill(0, 4, new DeclarationNote('note')),
        );
    }

    public function testShipmentShapeNeverContainsInvoiceFunction(): void
    {
        $declaration = new ExportDeclaration(lineItems: [$this->makeLineItem()], invoice: $this->makeInvoice());

        self::assertArrayNotHasKey('function', $declaration->toArray()['invoice']);
    }

    public function testInvoiceDataShapeContainsInvoiceFunction(): void
    {
        $declaration = new ExportDeclaration(
            lineItems: [$this->makeLineItem()],
            invoice: $this->makeInvoice(),
            incoterm: Incoterm::DAP,
            placeOfIncoterm: 'Prague',
        );

        $result = $declaration->toInvoiceDataArray();

        self::assertSame('export', $result['invoice']['function']);
        self::assertSame('DAP', $result['incoterm']);
        self::assertSame('Prague', $result['placeOfIncoterm']);
        self::assertCount(1, $result['lineItems']);
    }

    public function testCompleteUploadDeclarationHasNoInvoiceDataUploadErrors(): void
    {
        $declaration = new ExportDeclaration(
            lineItems: [$this->makeLineItem()],
            invoice: $this->makeInvoice(),
            incoterm: Incoterm::DAP,
        );

        self::assertSame([], $declaration->invoiceDataUploadErrors());
    }

    public function testInvoiceDataUploadErrorsNameEveryOffendingField(): void
    {
        $declaration = new ExportDeclaration(
            lineItems: [
                $this->makeLineItem(1),
                new ExportLineItem(
                    number: 2,
                    description: 'Test product',
                    price: 100.00,
                    quantity: new LineItemQuantity(5, LineItemQuantityUnit::PCS),
                    manufacturerCountry: 'CZ',
                    weight: new LineItemWeight(netValue: 1.0),
                    additionalInformation: ['info'],
                ),
            ],
            invoice: new ExportInvoice(
                number: 'INV-001',
                date: new DateTimeImmutable('2026-05-01'),
                signatureName: 'Jane Doe',
            ),
            exportReason: 'Sale',
            declarationNotes: [new DeclarationNote('note')],
            packageMarks: 'marks',
            payerVATNumber: '12345ED',
        );

        self::assertSame(
            [
                'incoterm',
                'invoice.function',
                'invoice.signatureName',
                'exportReason',
                'declarationNotes',
                'packageMarks',
                'payerVATNumber',
                'lineItems[1].additionalInformation',
            ],
            $declaration->invoiceDataUploadErrors(),
        );
    }

    public function testFieldsRemovedInApi340AreNeverSerialized(): void
    {
        $declaration = new ExportDeclaration(
            lineItems: [$this->makeLineItem()],
            invoice: $this->makeInvoice(),
            incoterm: Incoterm::DAP,
            exportReason: 'Sale',
            packageMarks: 'marks',
        );

        $result = $declaration->toArray();

        self::assertArrayNotHasKey('licenses', $result);
        self::assertArrayNotHasKey('exportReference', $result);
        self::assertArrayNotHasKey('destinationPortName', $result);
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
        $declaration = new ExportDeclaration(lineItems: [$this->makeLineItem()], invoice: $this->makeInvoice());

        $result = $declaration->toArray();

        self::assertArrayNotHasKey('incoterm', $result);
        self::assertArrayNotHasKey('exportReason', $result);
        self::assertArrayNotHasKey('declarationNotes', $result);
        self::assertArrayNotHasKey('packageMarks', $result);
        self::assertArrayNotHasKey('payerVATNumber', $result);
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

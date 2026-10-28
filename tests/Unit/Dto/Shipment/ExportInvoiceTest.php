<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use InvalidArgumentException;
use Medzuch\DhlExpress\Dto\Shipment\ExportInvoice;
use Medzuch\DhlExpress\Dto\Shipment\IndicativeCustomsValues;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceReference;
use Medzuch\DhlExpress\Dto\Shipment\PreCalculatedTotalValues;
use Medzuch\DhlExpress\Enum\InvoiceFunction;
use Medzuch\DhlExpress\Enum\InvoiceReferenceTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExportInvoiceTest extends TestCase
{
    public function testToArrayWithRequiredFieldsOnly(): void
    {
        $invoice = new ExportInvoice(
            number: 'INV-2026-001',
            date: new DateTimeImmutable('2026-05-01'),
            function: InvoiceFunction::Export,
        );

        $result = $invoice->toArray();

        self::assertSame('INV-2026-001', $result['number']);
        self::assertSame('2026-05-01', $result['date']);
        self::assertArrayNotHasKey('function', $result);
    }

    public function testToArrayWithOptionalFields(): void
    {
        $invoice = new ExportInvoice(
            number: 'INV-2026-001',
            date: new DateTimeImmutable('2026-05-01'),
            function: InvoiceFunction::Both,
            customerReferences: [new InvoiceReference(InvoiceReferenceTypeCode::PON, 'PO-12345')],
            indicativeCustomsValues: new IndicativeCustomsValues(importCustomsDutyValue: 15.00),
            preCalculatedTotalValues: new PreCalculatedTotalValues(
                preCalculatedTotalGoodsValue: 100.00,
                preCalculatedTotalInvoiceValue: 115.00,
            ),
        );

        $result = $invoice->toArray();

        self::assertArrayNotHasKey('function', $result);
        self::assertCount(1, $result['customerReferences']);
        self::assertSame('PON', $result['customerReferences'][0]['typeCode']);
        self::assertSame('PO-12345', $result['customerReferences'][0]['value']);
        self::assertSame(15.00, $result['indicativeCustomsValues']['importCustomsDutyValue']);
        self::assertSame(100.00, $result['preCalculatedTotalValues']['preCalculatedTotalGoodsValue']);
        self::assertSame(115.00, $result['preCalculatedTotalValues']['preCalculatedTotalInvoiceValue']);
    }

    public function testToArrayOmitsNullOptionals(): void
    {
        $invoice = new ExportInvoice(
            number: 'INV-2026-001',
            date: new DateTimeImmutable('2026-05-01'),
            function: InvoiceFunction::Export,
        );

        $result = $invoice->toArray();

        self::assertArrayNotHasKey('customerReferences', $result);
        self::assertArrayNotHasKey('indicativeCustomsValues', $result);
        self::assertArrayNotHasKey('preCalculatedTotalValues', $result);
        foreach (['signatureName', 'signatureTitle', 'signatureImage', 'instructions', 'customerDataTextEntries', 'totalNetWeight', 'totalGrossWeight', 'termsOfPayment'] as $key) {
            self::assertArrayNotHasKey($key, $result);
        }
    }

    public function testFunctionIsOptional(): void
    {
        $invoice = new ExportInvoice(number: 'INV-1', date: new DateTimeImmutable('2026-05-01'));

        self::assertNull($invoice->function);
        self::assertSame(['number' => 'INV-1', 'date' => '2026-05-01'], $invoice->toArray());
    }

    public function testInvoiceDataArrayIncludesFunctionAndUploadFieldsOnly(): void
    {
        $invoice = new ExportInvoice(
            number: 'INV-2026-001',
            date: new DateTimeImmutable('2026-05-01'),
            function: InvoiceFunction::Both,
            customerReferences: [new InvoiceReference(InvoiceReferenceTypeCode::PON, 'PO-12345')],
            indicativeCustomsValues: new IndicativeCustomsValues(importCustomsDutyValue: 15.00),
            preCalculatedTotalValues: new PreCalculatedTotalValues(
                preCalculatedTotalGoodsValue: 100.00,
                preCalculatedTotalInvoiceValue: 115.00,
            ),
        );

        $result = $invoice->toInvoiceDataArray();

        self::assertSame(
            ['number', 'date', 'function', 'customerReferences', 'indicativeCustomsValues', 'preCalculatedTotalValues'],
            array_keys($result),
        );
        self::assertSame('both', $result['function']);
    }

    public function testInvoiceDataArrayOmitsUnsetFunction(): void
    {
        $invoice = new ExportInvoice(number: 'INV-1', date: new DateTimeImmutable('2026-05-01'));

        self::assertSame(['number' => 'INV-1', 'date' => '2026-05-01'], $invoice->toInvoiceDataArray());
    }

    public function testShipmentOnlyFieldsListsEverySetSignatureAndSummaryField(): void
    {
        $plain = new ExportInvoice(number: 'INV-1', date: new DateTimeImmutable('2026-05-01'));
        $signed = new ExportInvoice(
            number: 'INV-1',
            date: new DateTimeImmutable('2026-05-01'),
            signatureName: 'Jane Doe',
            signatureTitle: 'Manager',
            signatureImage: 'aGVsbG8=',
            instructions: ['Keep dry'],
            customerDataTextEntries: ['Entry'],
            totalNetWeight: 1.0,
            totalGrossWeight: 1.2,
            termsOfPayment: '30 days',
        );

        self::assertSame([], $plain->shipmentOnlyFields());
        self::assertSame(
            ['signatureName', 'signatureTitle', 'signatureImage', 'instructions', 'customerDataTextEntries', 'totalNetWeight', 'totalGrossWeight', 'termsOfPayment'],
            $signed->shipmentOnlyFields(),
        );
    }

    public function testSignedInvoiceSerializesSignatureAndSummaryFields(): void
    {
        $invoice = new ExportInvoice(
            number: 'INV-2026-001',
            date: new DateTimeImmutable('2026-05-01'),
            function: InvoiceFunction::Export,
            signatureName: 'Jane Doe',
            signatureTitle: 'Export Manager',
            signatureImage: 'aGVsbG8=',
            instructions: ['Handle with care'],
            customerDataTextEntries: ['Entry one', 'Entry two'],
            totalNetWeight: 1.5,
            totalGrossWeight: 1.8,
            termsOfPayment: '100 days',
        );

        $result = $invoice->toArray();

        self::assertSame('Jane Doe', $result['signatureName']);
        self::assertSame('Export Manager', $result['signatureTitle']);
        self::assertSame('aGVsbG8=', $result['signatureImage']);
        self::assertSame(['Handle with care'], $result['instructions']);
        self::assertSame(['Entry one', 'Entry two'], $result['customerDataTextEntries']);
        self::assertSame(1.5, $result['totalNetWeight']);
        self::assertSame(1.8, $result['totalGrossWeight']);
        self::assertSame('100 days', $result['termsOfPayment']);
    }

    public function testFieldsAtTheirLimitsAreAccepted(): void
    {
        $invoice = new ExportInvoice(
            number: 'INV-1',
            date: new DateTimeImmutable('2026-05-01'),
            function: InvoiceFunction::Export,
            signatureName: str_repeat('a', 35),
            signatureTitle: str_repeat('b', 35),
            instructions: [str_repeat('c', 300)],
            customerDataTextEntries: array_fill(0, 6, str_repeat('d', 45)),
        );

        self::assertCount(6, $invoice->customerDataTextEntries);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function invalidOptionalFields(): iterable
    {
        yield 'signatureName too long' => [['signatureName' => str_repeat('a', 36)], 'signatureName'];
        yield 'signatureName empty' => [['signatureName' => ''], 'signatureName'];
        yield 'signatureTitle too long' => [['signatureTitle' => str_repeat('a', 36)], 'signatureTitle'];
        yield 'signatureImage empty' => [['signatureImage' => ''], 'signatureImage'];
        yield 'two instructions' => [['instructions' => ['a', 'b']], 'instructions'];
        yield 'instruction too long' => [['instructions' => [str_repeat('a', 301)]], 'instructions'];
        yield 'seven data text entries' => [['customerDataTextEntries' => array_fill(0, 7, 'a')], 'customerDataTextEntries'];
        yield 'data text entry too long' => [['customerDataTextEntries' => [str_repeat('a', 46)]], 'customerDataTextEntries'];
        yield 'negative net weight' => [['totalNetWeight' => -0.1], 'totalNetWeight'];
        yield 'negative gross weight' => [['totalGrossWeight' => -0.1], 'totalGrossWeight'];
    }

    /**
     * @param array<string, mixed> $overrides
     */
    #[DataProvider('invalidOptionalFields')]
    public function testInvalidOptionalFieldIsRejected(array $overrides, string $field): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($field);

        new ExportInvoice(...array_merge([
            'number' => 'INV-1',
            'date' => new DateTimeImmutable('2026-05-01'),
            'function' => InvoiceFunction::Export,
        ], $overrides));
    }

    public function testDateFormattedAsYmd(): void
    {
        $invoice = new ExportInvoice(
            number: 'INV-001',
            date: new DateTimeImmutable('2026-12-25'),
            function: InvoiceFunction::Import,
        );

        self::assertSame('2026-12-25', $invoice->toArray()['date']);
    }
}

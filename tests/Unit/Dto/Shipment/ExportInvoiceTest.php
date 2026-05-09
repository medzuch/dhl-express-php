<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Shipment\ExportInvoice;
use Medzuch\DhlExpress\Dto\Shipment\IndicativeCustomsValues;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceReference;
use Medzuch\DhlExpress\Dto\Shipment\PreCalculatedTotalValues;
use Medzuch\DhlExpress\Enum\InvoiceFunction;
use Medzuch\DhlExpress\Enum\InvoiceReferenceTypeCode;
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
        self::assertSame('export', $result['function']);
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

        self::assertSame('both', $result['function']);
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

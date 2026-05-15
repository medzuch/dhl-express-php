<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\GetImageDocumentTypeCode;
use PHPUnit\Framework\TestCase;

final class GetImageDocumentTypeCodeTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (GetImageDocumentTypeCode $case): string => $case->value, GetImageDocumentTypeCode::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testExpectedCasesExist(): void
    {
        $values = array_map(static fn (GetImageDocumentTypeCode $case): string => $case->value, GetImageDocumentTypeCode::cases());

        $expected = [
            'waybill',
            'commercial-invoice',
            'customs-entry',
            'transport-accompanying-document',
            'generic-entry-summary',
            'dhl-issued-proforma-invoice',
        ];

        foreach ($expected as $code) {
            self::assertContains($code, $values, "Missing case with value '{$code}'");
        }
        self::assertCount(count($expected), GetImageDocumentTypeCode::cases());
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(GetImageDocumentTypeCode::Waybill, GetImageDocumentTypeCode::from('waybill'));
        self::assertSame(GetImageDocumentTypeCode::CommercialInvoice, GetImageDocumentTypeCode::from('commercial-invoice'));
        self::assertSame(GetImageDocumentTypeCode::DhlIssuedProformaInvoice, GetImageDocumentTypeCode::from('dhl-issued-proforma-invoice'));
    }
}

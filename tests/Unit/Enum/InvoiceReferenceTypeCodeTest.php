<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\InvoiceReferenceTypeCode;
use PHPUnit\Framework\TestCase;

final class InvoiceReferenceTypeCodeTest extends TestCase
{
    public function testCoversAllFortyOneInvoiceReferenceCodes(): void
    {
        self::assertCount(41, InvoiceReferenceTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('PON', InvoiceReferenceTypeCode::PON->value);
        self::assertSame('HWB', InvoiceReferenceTypeCode::HWB->value);
        self::assertSame('MRN', InvoiceReferenceTypeCode::MRN->value);
        self::assertSame('ITN', InvoiceReferenceTypeCode::ITN->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(InvoiceReferenceTypeCode::PON, InvoiceReferenceTypeCode::from('PON'));
    }

    public function testEachBackingValueIsAtLeastTwoUppercaseLetters(): void
    {
        foreach (InvoiceReferenceTypeCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[A-Z]{2,4}$/', $case->value);
        }
    }
}

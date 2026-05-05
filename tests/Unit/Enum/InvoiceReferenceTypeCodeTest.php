<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\InvoiceReferenceTypeCode;
use PHPUnit\Framework\TestCase;

final class InvoiceReferenceTypeCodeTest extends TestCase
{
    public function testCoversAllNineteenInvoiceReferenceCodes(): void
    {
        self::assertCount(19, InvoiceReferenceTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('PON', InvoiceReferenceTypeCode::PON->value);
        self::assertSame('MRN', InvoiceReferenceTypeCode::MRN->value);
        self::assertSame('ITN', InvoiceReferenceTypeCode::ITN->value);
        self::assertSame('INB', InvoiceReferenceTypeCode::INB->value);
        self::assertSame('SME', InvoiceReferenceTypeCode::SME->value);
        self::assertSame('USM', InvoiceReferenceTypeCode::USM->value);
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

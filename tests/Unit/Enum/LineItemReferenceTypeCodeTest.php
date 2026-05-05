<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\LineItemReferenceTypeCode;
use PHPUnit\Framework\TestCase;

final class LineItemReferenceTypeCodeTest extends TestCase
{
    public function testCoversAllFortyOneLineItemCodes(): void
    {
        self::assertCount(41, LineItemReferenceTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('AFE', LineItemReferenceTypeCode::AFE->value);
        self::assertSame('BRD', LineItemReferenceTypeCode::BRD->value);
        self::assertSame('DGC', LineItemReferenceTypeCode::DGC->value);
        self::assertSame('PON', LineItemReferenceTypeCode::PON->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(LineItemReferenceTypeCode::AFE, LineItemReferenceTypeCode::from('AFE'));
    }

    public function testEachBackingValueIsTwoToFourUppercaseLetters(): void
    {
        foreach (LineItemReferenceTypeCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[A-Z]{2,4}$/', $case->value);
        }
    }
}

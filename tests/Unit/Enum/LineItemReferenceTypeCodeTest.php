<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\LineItemReferenceTypeCode;
use PHPUnit\Framework\TestCase;

final class LineItemReferenceTypeCodeTest extends TestCase
{
    public function testCoversAllFortyFourLineItemCodes(): void
    {
        self::assertCount(44, LineItemReferenceTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('AFE', LineItemReferenceTypeCode::AFE->value);
        self::assertSame('BRD', LineItemReferenceTypeCode::BRD->value);
        self::assertSame('DGC', LineItemReferenceTypeCode::DGC->value);
        self::assertSame('PON', LineItemReferenceTypeCode::PON->value);
        self::assertSame('AKO', LineItemReferenceTypeCode::AKO->value);
        self::assertSame('MF', LineItemReferenceTypeCode::MF->value);
        self::assertSame('SRV', LineItemReferenceTypeCode::SRV->value);
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

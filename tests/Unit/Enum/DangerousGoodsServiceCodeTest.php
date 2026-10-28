<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DangerousGoodsServiceCode;
use PHPUnit\Framework\TestCase;

final class DangerousGoodsServiceCodeTest extends TestCase
{
    public function testCoversSeventeenServiceCodes(): void
    {
        self::assertCount(17, DangerousGoodsServiceCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('HY', DangerousGoodsServiceCode::HY->value);
        self::assertSame('HU', DangerousGoodsServiceCode::HU->value);
        self::assertSame('HE', DangerousGoodsServiceCode::HE->value);
        self::assertSame('HA', DangerousGoodsServiceCode::HA->value);
        self::assertSame('HB', DangerousGoodsServiceCode::HB->value);
        self::assertSame('YN', DangerousGoodsServiceCode::YN->value);
        self::assertSame('H8', DangerousGoodsServiceCode::H8->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(DangerousGoodsServiceCode::HY, DangerousGoodsServiceCode::from('HY'));
    }

    public function testEachBackingValueIsTwoUppercaseAlphanumerics(): void
    {
        foreach (DangerousGoodsServiceCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[A-Z][A-Z0-9]$/', $case->value);
        }
    }
}

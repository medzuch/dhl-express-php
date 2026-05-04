<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DangerousGoodsServiceCode;
use PHPUnit\Framework\TestCase;

final class DangerousGoodsServiceCodeTest extends TestCase
{
    public function testCoversThirteenServiceCodes(): void
    {
        self::assertCount(13, DangerousGoodsServiceCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('HY', DangerousGoodsServiceCode::HY->value);
        self::assertSame('HU', DangerousGoodsServiceCode::HU->value);
        self::assertSame('HE', DangerousGoodsServiceCode::HE->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(DangerousGoodsServiceCode::HY, DangerousGoodsServiceCode::from('HY'));
    }

    public function testEachBackingValueIsTwoUppercaseLettersStartingWithH(): void
    {
        foreach (DangerousGoodsServiceCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^H[A-Z]$/', $case->value);
        }
    }
}

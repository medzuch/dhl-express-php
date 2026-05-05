<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DangerousGoodsContentId;
use PHPUnit\Framework\TestCase;

final class DangerousGoodsContentIdTest extends TestCase
{
    public function testCoversTwentyContentIds(): void
    {
        self::assertCount(20, DangerousGoodsContentId::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('650', DangerousGoodsContentId::BiologicalSubstanceUN3373->value);
        self::assertSame('901', DangerousGoodsContentId::DryIceUN1845->value);
        self::assertSame('A01', DangerousGoodsContentId::A01->value);
        self::assertSame('970', DangerousGoodsContentId::LithiumMetalPI970->value);
    }

    public function testFromCanResolveDigitPrefixedWireCode(): void
    {
        self::assertSame(
            DangerousGoodsContentId::BiologicalSubstanceUN3373,
            DangerousGoodsContentId::from('650'),
        );
    }

    public function testFromCanResolveAlphaPrefixedWireCode(): void
    {
        self::assertSame(DangerousGoodsContentId::A01, DangerousGoodsContentId::from('A01'));
    }
}

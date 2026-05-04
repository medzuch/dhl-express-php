<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use PHPUnit\Framework\TestCase;

final class DimensionUnitTest extends TestCase
{
    public function testCentimeterBackingValueIsTheUnitSymbol(): void
    {
        self::assertSame('CM', DimensionUnit::CM->value);
    }

    public function testInchBackingValueIsTheUnitSymbol(): void
    {
        self::assertSame('IN', DimensionUnit::IN->value);
    }

    public function testCentimeterReportsMetricSystem(): void
    {
        self::assertSame(UnitSystem::Metric, DimensionUnit::CM->system());
    }

    public function testInchReportsImperialSystem(): void
    {
        self::assertSame(UnitSystem::Imperial, DimensionUnit::IN->system());
    }

    public function testCoversExactlyTwoCases(): void
    {
        self::assertCount(2, DimensionUnit::cases());
    }
}

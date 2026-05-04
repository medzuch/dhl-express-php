<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use PHPUnit\Framework\TestCase;

final class WeightUnitTest extends TestCase
{
    public function testKilogramBackingValueIsTheUnitSymbol(): void
    {
        self::assertSame('KG', WeightUnit::KG->value);
    }

    public function testPoundBackingValueIsTheUnitSymbol(): void
    {
        self::assertSame('LB', WeightUnit::LB->value);
    }

    public function testKilogramReportsMetricSystem(): void
    {
        self::assertSame(UnitSystem::Metric, WeightUnit::KG->system());
    }

    public function testPoundReportsImperialSystem(): void
    {
        self::assertSame(UnitSystem::Imperial, WeightUnit::LB->system());
    }

    public function testCoversExactlyTwoCases(): void
    {
        self::assertCount(2, WeightUnit::cases());
    }
}

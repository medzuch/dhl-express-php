<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class WeightTest extends TestCase
{
    public function testStoresValueAndUnit(): void
    {
        $weight = new Weight(2.5, WeightUnit::KG);

        self::assertSame(2.5, $weight->value);
        self::assertSame(WeightUnit::KG, $weight->unit);
    }

    public function testReportsParentUnitSystem(): void
    {
        self::assertSame(UnitSystem::Metric, (new Weight(1.0, WeightUnit::KG))->system());
        self::assertSame(UnitSystem::Imperial, (new Weight(1.0, WeightUnit::LB))->system());
    }

    public function testEqualityRequiresMatchingValueAndUnit(): void
    {
        $a = new Weight(2.5, WeightUnit::KG);
        $b = new Weight(2.5, WeightUnit::KG);
        $c = new Weight(2.5, WeightUnit::LB);
        $d = new Weight(3.0, WeightUnit::KG);

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
        self::assertFalse($a->equals($d));
    }

    public function testRejectsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('positive');

        new Weight(0.0, WeightUnit::KG);
    }

    public function testRejectsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Weight(-1.0, WeightUnit::KG);
    }
}

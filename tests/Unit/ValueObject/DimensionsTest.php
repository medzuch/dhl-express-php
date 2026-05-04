<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use PHPUnit\Framework\TestCase;

final class DimensionsTest extends TestCase
{
    public function testStoresLengthWidthHeightAndUnit(): void
    {
        $dimensions = new Dimensions(30.0, 20.0, 15.0, DimensionUnit::CM);

        self::assertSame(30.0, $dimensions->length);
        self::assertSame(20.0, $dimensions->width);
        self::assertSame(15.0, $dimensions->height);
        self::assertSame(DimensionUnit::CM, $dimensions->unit);
    }

    public function testReportsParentUnitSystem(): void
    {
        self::assertSame(UnitSystem::Metric, (new Dimensions(1.0, 1.0, 1.0, DimensionUnit::CM))->system());
        self::assertSame(UnitSystem::Imperial, (new Dimensions(1.0, 1.0, 1.0, DimensionUnit::IN))->system());
    }

    public function testEqualityRequiresAllFieldsToMatch(): void
    {
        $a = new Dimensions(30.0, 20.0, 15.0, DimensionUnit::CM);
        $b = new Dimensions(30.0, 20.0, 15.0, DimensionUnit::CM);
        $c = new Dimensions(30.0, 20.0, 15.0, DimensionUnit::IN);
        $d = new Dimensions(30.0, 20.0, 16.0, DimensionUnit::CM);

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
        self::assertFalse($a->equals($d));
    }

    public function testRejectsZeroLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('positive');

        new Dimensions(0.0, 20.0, 15.0, DimensionUnit::CM);
    }

    public function testRejectsNegativeWidth(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Dimensions(30.0, -1.0, 15.0, DimensionUnit::CM);
    }

    public function testRejectsZeroHeight(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Dimensions(30.0, 20.0, 0.0, DimensionUnit::CM);
    }
}

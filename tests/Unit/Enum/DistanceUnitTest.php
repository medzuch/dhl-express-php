<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DistanceUnit;
use PHPUnit\Framework\TestCase;

final class DistanceUnitTest extends TestCase
{
    public function testKilometerBackingValueMatchesDhlWireFormat(): void
    {
        self::assertSame('km', DistanceUnit::KM->value);
    }

    public function testMileBackingValueMatchesDhlWireFormat(): void
    {
        self::assertSame('mi', DistanceUnit::MI->value);
    }

    public function testCoversTwoCases(): void
    {
        self::assertCount(2, DistanceUnit::cases());
    }
}

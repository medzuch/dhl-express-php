<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\UnitSystem;
use PHPUnit\Framework\TestCase;

final class UnitSystemTest extends TestCase
{
    public function testMetricBackingValueMatchesDhlWireFormat(): void
    {
        self::assertSame('metric', UnitSystem::Metric->value);
    }

    public function testImperialBackingValueMatchesDhlWireFormat(): void
    {
        self::assertSame('imperial', UnitSystem::Imperial->value);
    }

    public function testFromMetricStringYieldsMetricCase(): void
    {
        self::assertSame(UnitSystem::Metric, UnitSystem::from('metric'));
    }

    public function testFromImperialStringYieldsImperialCase(): void
    {
        self::assertSame(UnitSystem::Imperial, UnitSystem::from('imperial'));
    }

    public function testCoversExactlyTwoCases(): void
    {
        self::assertCount(2, UnitSystem::cases());
    }
}

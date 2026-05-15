<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\TrackingLevelOfDetail;
use PHPUnit\Framework\TestCase;

final class TrackingLevelOfDetailTest extends TestCase
{
    public function testCoversThreeLevels(): void
    {
        self::assertCount(3, TrackingLevelOfDetail::cases());
    }

    public function testBackingValuesMatchDhlWireFormat(): void
    {
        self::assertSame('shipment', TrackingLevelOfDetail::Shipment->value);
        self::assertSame('piece', TrackingLevelOfDetail::Piece->value);
        self::assertSame('all', TrackingLevelOfDetail::All->value);
    }

    public function testFromResolvesKnownValue(): void
    {
        self::assertSame(TrackingLevelOfDetail::Piece, TrackingLevelOfDetail::from('piece'));
    }
}

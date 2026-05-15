<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\TrackingView;
use PHPUnit\Framework\TestCase;

final class TrackingViewTest extends TestCase
{
    public function testCoversSixTrackingViews(): void
    {
        self::assertCount(6, TrackingView::cases());
    }

    public function testBackingValuesMatchDhlWireFormat(): void
    {
        self::assertSame('all-checkpoints', TrackingView::AllCheckpoints->value);
        self::assertSame('all-checkpoints-with-remarks', TrackingView::AllCheckpointsWithRemarks->value);
        self::assertSame('last-checkpoint', TrackingView::LastCheckpoint->value);
        self::assertSame('shipment-details-only', TrackingView::ShipmentDetailsOnly->value);
        self::assertSame('advance-shipment', TrackingView::AdvanceShipment->value);
        self::assertSame('bbx-children', TrackingView::BbxChildren->value);
    }

    public function testFromResolvesKnownValue(): void
    {
        self::assertSame(TrackingView::LastCheckpoint, TrackingView::from('last-checkpoint'));
    }
}

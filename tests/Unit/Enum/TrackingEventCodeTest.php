<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\TrackingEventCode;
use PHPUnit\Framework\TestCase;

final class TrackingEventCodeTest extends TestCase
{
    public function testCoversAllSixtyFiveTrackingEventCodes(): void
    {
        self::assertCount(65, TrackingEventCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('OK', TrackingEventCode::OK->value);
        self::assertSame('PU', TrackingEventCode::PU->value);
        self::assertSame('CR', TrackingEventCode::CR->value);
        self::assertSame('RT', TrackingEventCode::RT->value);
        self::assertSame('SD', TrackingEventCode::SD->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(TrackingEventCode::OK, TrackingEventCode::from('OK'));
    }

    public function testEachBackingValueIsTwoUppercaseLetters(): void
    {
        foreach (TrackingEventCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[A-Z]{2}$/', $case->value);
        }
    }
}

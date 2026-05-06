<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ServicePoint;

use Medzuch\DhlExpress\Enum\DayOfWeek;

/**
 * One opening-hours slot on a Service Point.
 *
 * `dayOfWeek` is null when DHL returns a value outside the documented
 * `MONDAY..SUNDAY` / `HOLIDAY` set — we surface the raw value via
 * {@see $rawDayOfWeek} so callers can still see what came back.
 */
final readonly class OpeningTime
{
    public function __construct(
        public ?DayOfWeek $dayOfWeek,
        public string $rawDayOfWeek,
        public string $openingTime,
        public string $closingTime,
    ) {
    }
}

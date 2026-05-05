<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Weekday for which an opening-hours entry is valid.
 *
 * Mirrors the OpenAPI `dayOfWeek` enum on Service Point opening
 * hours. The eighth case `HOLIDAY` represents publicly observed
 * holidays distinct from the seven recurring weekdays.
 */
enum DayOfWeek: string
{
    case Monday = 'MONDAY';
    case Tuesday = 'TUESDAY';
    case Wednesday = 'WEDNESDAY';
    case Thursday = 'THURSDAY';
    case Friday = 'FRIDAY';
    case Saturday = 'SATURDAY';
    case Sunday = 'SUNDAY';
    case Holiday = 'HOLIDAY';
}

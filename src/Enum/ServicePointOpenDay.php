<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Day-of-week filter for the `/servicepoints` `openDay` query
 * parameter.
 *
 * Distinct from {@see DayOfWeek} (which carries `MONDAY` / `TUESDAY`
 * uppercase values used on opening-hours response payloads). The
 * `openDay` filter uses numeric strings with Sunday = 0.
 */
enum ServicePointOpenDay: string
{
    case Sunday = '0';
    case Monday = '1';
    case Tuesday = '2';
    case Wednesday = '3';
    case Thursday = '4';
    case Friday = '5';
    case Saturday = '6';
}

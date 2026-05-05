<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Operational category of a DHL Service Point.
 *
 * From the OpenAPI Service Point search response: a DHL-staffed
 * city counter, a self-service shipping station, a partner-operated
 * dropoff (newsagent, courier, etc.) or a 24/7 location.
 */
enum ServicePointType: string
{
    case City = 'CITY';
    case Station = 'STATION';
    case Partner = 'PARTNER';
    case TwentyFourSeven = 'TWENTYFOURSEVEN';
}

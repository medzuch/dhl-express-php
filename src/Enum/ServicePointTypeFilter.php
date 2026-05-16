<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Service-point type filter accepted by the `/servicepoints`
 * `servicePointTypes` query parameter.
 *
 * Distinct from {@see ServicePointType}: DHL uses two different wire
 * vocabularies for the same four concepts. The request-side filter
 * uses 3-letter abbreviations (`CTY`, `STN`, `PRT`, `247`); the
 * response-side hydrated value uses the long forms (`CITY`,
 * `STATION`, `PARTNER`, `TWENTYFOURSEVEN`).
 */
enum ServicePointTypeFilter: string
{
    case City = 'CTY';
    case Station = 'STN';
    case Partner = 'PRT';
    case TwentyFourSeven = '247';
}

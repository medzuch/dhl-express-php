<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Unit for distances reported by the Service Point search.
 *
 * Distinct from {@see DimensionUnit} — Service Point distances are
 * line-of-sight from the requested address, expressed in either
 * kilometers or miles depending on the caller's preference.
 */
enum DistanceUnit: string
{
    case KM = 'km';
    case MI = 'mi';
}

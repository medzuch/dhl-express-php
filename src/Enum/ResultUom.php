<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Distance unit applied to a `/servicepoints` response.
 *
 * Controls the unit used for distances on returned service points
 * (`km` or `mi`). If omitted, DHL falls back to the country-configured
 * default (km for metric countries, mi for the UK/US).
 */
enum ResultUom: string
{
    case Kilometers = 'km';
    case Miles = 'mi';
}

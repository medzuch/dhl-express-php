<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * MyDHL API landed-cost estimated rate type.
 *
 * Six values from the Reference Data Guide section 6 controlling
 * how the GTS service derives import duties and taxes — the choice
 * trades off precision for ease of use (full HS code vs 6-digit
 * fallback, default rate vs preferential treatment).
 */
enum LandedCostRateType: string
{
    case DefaultRate = 'default_rate';
    case DerivedRate = 'derived_rate';
    case HighestRate = 'highest_rate';
    case CenterRate = 'center_rate';
    case LowestRate = 'lowest_rate';
    case PreferentialRate = 'preferential_rate';
}

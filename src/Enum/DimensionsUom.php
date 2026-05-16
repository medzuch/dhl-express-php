<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Dimension unit accepted by the `/servicepoints` `dimensionsUom`
 * query parameter.
 *
 * Distinct from {@see DimensionUnit} (shipment-side). Servicepoint API
 * accepts `cm` or `in` as the on-wire value.
 */
enum DimensionsUom: string
{
    case Centimeters = 'cm';
    case Inches = 'in';
}

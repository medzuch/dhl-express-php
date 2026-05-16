<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Weight unit accepted by the `/servicepoints` `weightUom` query parameter.
 *
 * Distinct from {@see WeightUnit} (which is the shipment-side weight
 * unit). DHL's servicepoint API uses the short `kg` / `lb` codes
 * directly as the wire value.
 */
enum WeightUom: string
{
    case Kilograms = 'kg';
    case Pounds = 'lb';
}

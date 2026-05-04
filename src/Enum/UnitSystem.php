<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * The unit system that governs an entire DHL shipment.
 *
 * DHL's MyDHL API does not let individual weights or dimensions
 * carry their own unit — the shipment as a whole is `metric` or
 * `imperial` and every numeric value is interpreted under that
 * system. This enum is the wire-format value DHL writes and reads
 * in the `unitOfMeasurement` field; concrete unit symbols (kg, lb,
 * cm, in) live on {@see WeightUnit} / {@see DimensionUnit} and
 * report their parent system via `->system()`.
 */
enum UnitSystem: string
{
    case Metric = 'metric';
    case Imperial = 'imperial';
}

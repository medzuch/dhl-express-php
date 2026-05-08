<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

/**
 * Weight block returned for a quoted product — DHL's volumetric vs.
 * provided weight comparison.
 *
 * The wire `unitOfMeasurement` is `metric` or `imperial`; we keep it
 * as a raw string so callers can branch on it without us pre-mapping
 * to `UnitSystem`.
 */
final readonly class QuotedWeight
{
    public function __construct(
        public ?float $volumetric,
        public ?float $provided,
        public string $unitOfMeasurement,
    ) {
    }
}

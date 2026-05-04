<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;

/**
 * A weight value paired with the unit it is expressed in.
 *
 * Constructor enforces strict positivity (DHL's package weight
 * minimum is 0.001). The DHL `unitOfMeasurement` field is
 * shipment-wide; serializers and the CreateShipmentBuilder cross-
 * field check pull it via {@see self::system()}.
 */
final readonly class Weight
{
    public function __construct(
        public float $value,
        public WeightUnit $unit,
    ) {
        if ($value <= 0) {
            throw new InvalidArgumentException("Weight must be positive; got {$value}.");
        }
    }

    public function system(): UnitSystem
    {
        return $this->unit->system();
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value && $this->unit === $other->unit;
    }
}

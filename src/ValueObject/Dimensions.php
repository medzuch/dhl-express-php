<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;

/**
 * Linear length × width × height with the unit they are expressed in.
 *
 * Constructor enforces strict positivity for all three axes (DHL
 * minimum is 0.001 per axis). The unit system propagates via
 * {@see self::system()} the same way {@see Weight} does it.
 */
final readonly class Dimensions
{
    public function __construct(
        public float $length,
        public float $width,
        public float $height,
        public DimensionUnit $unit,
    ) {
        $this->assertPositive('length', $length);
        $this->assertPositive('width', $width);
        $this->assertPositive('height', $height);
    }

    public function system(): UnitSystem
    {
        return $this->unit->system();
    }

    public function equals(self $other): bool
    {
        return $this->length === $other->length
            && $this->width === $other->width
            && $this->height === $other->height
            && $this->unit === $other->unit;
    }

    private function assertPositive(string $axis, float $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Dimension {$axis} must be positive; got {$value}.");
        }
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * Three-letter DHL service area code (e.g. `SYD`, `PRG`, `MAA`).
 *
 * The OpenAPI spec doesn't formally constrain the shape, but every
 * example in the spec and the reference PDF is uniformly three
 * uppercase letters — codes are derived from IATA airport codes,
 * which share that format.
 */
final readonly class ServiceAreaCode implements Stringable
{
    public function __construct(public string $value)
    {
        if (preg_match('/^[A-Z]{3}$/', $value) !== 1) {
            throw new InvalidArgumentException(
                "Service area code must be three uppercase letters; got '{$value}'.",
            );
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

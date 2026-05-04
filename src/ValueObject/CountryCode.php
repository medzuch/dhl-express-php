<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * ISO 3166-1 alpha-2 country code (two uppercase letters).
 *
 * Format only — we don't enumerate the ~250 official codes
 * client-side. Maintaining the list is its own problem and
 * unknown codes are rejected by DHL with a 400, which surfaces as
 * {@see \Medzuch\DhlExpress\Exception\DhlValidationException}.
 */
final readonly class CountryCode implements Stringable
{
    public function __construct(public string $value)
    {
        if (preg_match('/^[A-Z]{2}$/', $value) !== 1) {
            throw new InvalidArgumentException(
                "Country code must be ISO 3166-1 alpha-2 (two uppercase letters); got '{$value}'.",
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

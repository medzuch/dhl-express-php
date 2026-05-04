<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * ISO 4217 currency code (three uppercase letters).
 *
 * Format only — we don't enumerate the ~180 official codes
 * client-side. DHL rejects unknown codes with a 400 which surfaces
 * as {@see \Medzuch\DhlExpress\Exception\DhlValidationException}.
 */
final readonly class CurrencyCode implements Stringable
{
    public function __construct(public string $value)
    {
        if (preg_match('/^[A-Z]{3}$/', $value) !== 1) {
            throw new InvalidArgumentException(
                "Currency code must be ISO 4217 (three uppercase letters); got '{$value}'.",
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

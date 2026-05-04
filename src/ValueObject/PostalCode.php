<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * Postal code constrained to DHL's 0-12 character window.
 *
 * Empty string is intentionally allowed — Hong Kong, the UAE, and a
 * handful of other DHL destinations have no postal code system, and
 * DHL's spec treats an empty string as the canonical representation.
 * Country-aware format validation belongs in a future builder pass
 * once we have the country-to-format mapping; for now we let the
 * server validate.
 */
final readonly class PostalCode implements Stringable
{
    public function __construct(public string $value)
    {
        $length = strlen($value);

        if ($length > 12) {
            throw new InvalidArgumentException(
                "Postal code must be at most 12 characters; got {$length}.",
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

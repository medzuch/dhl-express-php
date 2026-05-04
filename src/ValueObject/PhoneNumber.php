<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * Phone number constrained to DHL's contact field length (1-70).
 *
 * No format pattern is enforced — DHL accepts a wide range of
 * country-specific formats (with or without `+`, hyphens, spaces,
 * parentheses) and validating against a strict shape would reject
 * inputs DHL itself would accept.
 */
final readonly class PhoneNumber implements Stringable
{
    public function __construct(public string $value)
    {
        $length = strlen($value);

        if ($length < 1 || $length > 70) {
            throw new InvalidArgumentException(
                "Phone number must be 1-70 characters; got {$length}.",
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

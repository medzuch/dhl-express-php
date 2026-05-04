<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * Harmonized System code (commodity code) for customs declarations.
 *
 * The OpenAPI spec caps the field at 18 characters and notes "can be
 * provided with or without dots", so we accept digits and dots. The
 * canonical 6-digit form (`851713`) and the dotted local extension
 * forms (`8517.13.00`) both pass.
 */
final readonly class HsCode implements Stringable
{
    public function __construct(public string $value)
    {
        $length = strlen($value);

        if ($length < 1 || $length > 18) {
            throw new InvalidArgumentException(
                "HS code must be 1-18 characters; got {$length}.",
            );
        }

        if (preg_match('/^[0-9.]+$/', $value) !== 1) {
            throw new InvalidArgumentException(
                "HS code must contain only digits and dots; got '{$value}'.",
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

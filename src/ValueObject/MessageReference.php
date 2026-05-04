<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * DHL Message-Reference header value.
 *
 * @see https://express.api.dhl.com — Message-Reference header schema (string, minLength 1, maxLength 36).
 */
final readonly class MessageReference implements Stringable
{
    public function __construct(public string $value)
    {
        $length = strlen($value);

        if ($length < 1 || $length > 36) {
            throw new InvalidArgumentException('Message reference must be 1-36 characters');
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

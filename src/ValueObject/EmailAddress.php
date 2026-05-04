<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * Email address constrained to DHL's contact field length (1-70).
 *
 * Format check is intentionally light — `filter_var` with
 * `FILTER_VALIDATE_EMAIL` rejects addresses with non-ASCII parts and
 * a few other technically-valid forms; we only require an `@` plus
 * something on either side. DHL is the authority on whether an
 * address is deliverable.
 */
final readonly class EmailAddress implements Stringable
{
    public function __construct(public string $value)
    {
        $length = strlen($value);

        if ($length < 1 || $length > 70) {
            throw new InvalidArgumentException(
                "Email address must be 1-70 characters; got {$length}.",
            );
        }

        if (preg_match('/^[^@\s]+@[^@\s]+$/', $value) !== 1) {
            throw new InvalidArgumentException(
                "Value '{$value}' is not a valid email address.",
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

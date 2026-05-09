<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * DHL Express customer account number.
 *
 * The OpenAPI spec doesn't constrain length or charset; canonical
 * examples are 9-digit numerics like `123456789`. We only require a
 * non-empty string and let DHL reject malformed accounts.
 */
final readonly class AccountNumber implements Stringable
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Account number must be a non-empty string.');
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

    /**
     * Mask the account number when var_dump'd or logged. Same idea as
     * {@see \Medzuch\DhlExpress\Auth\Credentials::__debugInfo()}: a value
     * leaking into a log file is one of the most common ways to expose
     * customer account numbers, and the masked form keeps the last four
     * digits to keep diagnostics useful.
     *
     * @return array{value: string}
     */
    public function __debugInfo(): array
    {
        $length = strlen($this->value);
        if ($length <= 4) {
            return ['value' => str_repeat('*', $length)];
        }

        return ['value' => str_repeat('*', $length - 4) . substr($this->value, -4)];
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;

/**
 * A monetary amount in a specific currency.
 *
 * Negative and zero amounts are accepted — DHL fields are mixed:
 * declared values must be positive, refunds and credit lines are
 * negative. Field-specific positivity belongs in builders (Layer 2),
 * not here. The constructor only rejects non-finite values that
 * could never represent real money.
 */
final readonly class Money
{
    public function __construct(
        public float $amount,
        public CurrencyCode $currency,
    ) {
        if (!is_finite($amount)) {
            throw new InvalidArgumentException("Money amount must be a finite number; got {$amount}.");
        }
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency->equals($other->currency);
    }
}

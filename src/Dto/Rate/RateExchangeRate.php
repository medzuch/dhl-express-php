<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

/**
 * Exchange rate row from the response — DHL returns these so callers
 * can convert between billing, base, and public-rates currencies.
 */
final readonly class RateExchangeRate
{
    public function __construct(
        public float $currentExchangeRate,
        public string $currency,
        public string $baseCurrency,
    ) {
    }
}

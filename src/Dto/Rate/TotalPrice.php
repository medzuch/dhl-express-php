<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\RateCurrencyType;

/**
 * One total-price line for a quoted product. DHL returns the same
 * total in up to three currencies (billing, public-rates, base) so
 * `totalPrices` is always a list.
 *
 * Unknown wire values for `currencyType` flow through as the raw
 * string in {@see self::$rawCurrencyType}.
 */
final readonly class TotalPrice
{
    public function __construct(
        public ?RateCurrencyType $currencyType,
        public string $rawCurrencyType,
        public string $priceCurrency,
        public float $price,
    ) {
    }
}

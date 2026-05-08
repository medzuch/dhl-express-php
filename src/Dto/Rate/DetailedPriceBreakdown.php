<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\RateCurrencyType;

/**
 * Currency-scoped detailed breakdown — one per `currencyType`.
 */
final readonly class DetailedPriceBreakdown
{
    /**
     * @param list<BreakdownDetail> $breakdown
     */
    public function __construct(
        public ?RateCurrencyType $currencyType,
        public string $rawCurrencyType,
        public string $priceCurrency,
        public array $breakdown,
    ) {
    }
}

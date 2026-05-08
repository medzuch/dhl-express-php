<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\RateCurrencyType;

/**
 * Currency-scoped rolled-up breakdown — one of these for each
 * `currencyType` DHL returns (BILLC / PULCL / BASEC).
 */
final readonly class TotalPriceBreakdown
{
    /**
     * @param list<PriceBreakdownItem> $priceBreakdown
     */
    public function __construct(
        public ?RateCurrencyType $currencyType,
        public string $rawCurrencyType,
        public string $priceCurrency,
        public array $priceBreakdown,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

/**
 * Top-level response returned by `/rates` (GET and POST) and
 * `/landed-cost`. DHL uses one schema for all three.
 */
final readonly class RatesResponse
{
    /**
     * @param list<QuotedProduct>     $products
     * @param list<RateExchangeRate>  $exchangeRates
     * @param list<string>            $warnings
     */
    public function __construct(
        public array $products,
        public array $exchangeRates = [],
        public array $warnings = [],
    ) {
    }
}

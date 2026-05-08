<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

/**
 * One row of the `detailedPriceBreakdown.breakdown[]` — the rich
 * per-service / per-charge view DHL returns when the consumer asks
 * for it. `priceBreakdown` here is the inner tax/discount split
 * captured as raw arrays so we don't fan out yet another DTO until
 * a caller actually needs it.
 */
final readonly class BreakdownDetail
{
    /**
     * @param list<array<string, mixed>> $priceBreakdown
     */
    public function __construct(
        public string $name,
        public ?string $serviceCode,
        public ?string $localServiceCode,
        public ?string $typeCode,
        public ?string $serviceTypeCode,
        public float $price,
        public ?string $priceCurrency,
        public ?bool $isCustomerAgreement,
        public ?bool $isMarketedService,
        public ?bool $isBillingServiceIndicator,
        public array $priceBreakdown,
        public ?string $tariffRateFormula,
    ) {
    }
}

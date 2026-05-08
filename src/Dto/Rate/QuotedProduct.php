<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\RateNetworkTypeCode;

/**
 * One quoted product entry returned by `/rates` or `/landed-cost`.
 *
 * Captures the most useful fields callers need today; richer DHL
 * data (mutually-exclusive service-code groups, rule-group
 * dependencies, raw item-level breakdowns from /landed-cost) sits in
 * {@see self::$rawItems} so a future caller can fan out without
 * needing a hydrator change first.
 */
final readonly class QuotedProduct
{
    /**
     * @param list<TotalPrice>             $totalPrices
     * @param list<TotalPriceBreakdown>    $totalPriceBreakdowns
     * @param list<DetailedPriceBreakdown> $detailedPriceBreakdowns
     * @param array<string, mixed>|null    $pickupCapabilities
     * @param list<array<string, mixed>>   $rawItems Item-level detail returned by /landed-cost
     */
    public function __construct(
        public string $productName,
        public string $productCode,
        public ?string $localProductCode,
        public ?string $localProductCountryCode,
        public ?RateNetworkTypeCode $networkTypeCode,
        public string $rawNetworkTypeCode,
        public ?bool $isCustomerAgreement,
        public ?QuotedWeight $weight,
        public array $totalPrices,
        public array $totalPriceBreakdowns,
        public array $detailedPriceBreakdowns,
        public ?array $pickupCapabilities,
        public ?DeliveryCapability $deliveryCapabilities,
        public ?EstimatedDeliveryDate $estimatedDeliveryDate,
        public array $rawItems,
    ) {
    }
}

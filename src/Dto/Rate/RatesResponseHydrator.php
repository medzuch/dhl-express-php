<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateTypeCode;
use Medzuch\DhlExpress\Enum\RateCurrencyType;
use Medzuch\DhlExpress\Enum\RateNetworkTypeCode;
use Medzuch\DhlExpress\Support\HydrationHelper;

/**
 * Hydrates the shared `/rates` and `/landed-cost` JSON response into
 * the typed {@see RatesResponse} graph. Lives in the Dto namespace
 * because it is purely a data-shape concern.
 *
 * Stateless — every method is a pure mapping. Kept as instance
 * methods (not static) so it can be mocked or injected if a future
 * test needs it.
 */
final class RatesResponseHydrator
{
    /**
     * @param array<string, mixed> $body
     */
    public function hydrate(array $body): RatesResponse
    {
        $products = [];
        $rawProducts = $body['products'] ?? null;
        if (is_array($rawProducts)) {
            foreach ($rawProducts as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                /** @var array<string, mixed> $entry */
                $products[] = $this->hydrateProduct($entry);
            }
        }

        $exchangeRates = [];
        $rawExchangeRates = $body['exchangeRates'] ?? null;
        if (is_array($rawExchangeRates)) {
            foreach ($rawExchangeRates as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                /** @var array<string, mixed> $entry */
                $exchangeRates[] = new RateExchangeRate(
                    currentExchangeRate: HydrationHelper::floatField($entry, 'currentExchangeRate') ?? 0.0,
                    currency: HydrationHelper::stringField($entry, 'currency'),
                    baseCurrency: HydrationHelper::stringField($entry, 'baseCurrency'),
                );
            }
        }

        $warnings = [];
        $rawWarnings = $body['warnings'] ?? null;
        if (is_array($rawWarnings)) {
            foreach ($rawWarnings as $warning) {
                if (is_string($warning)) {
                    $warnings[] = $warning;
                }
            }
        }

        return new RatesResponse(
            products: $products,
            exchangeRates: $exchangeRates,
            warnings: $warnings,
        );
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function hydrateProduct(array $raw): QuotedProduct
    {
        $rawNetworkTypeCode = HydrationHelper::stringField($raw, 'networkTypeCode');

        $rawItems = [];
        if (isset($raw['items']) && is_array($raw['items'])) {
            foreach ($raw['items'] as $item) {
                if (is_array($item)) {
                    /** @var array<string, mixed> $item */
                    $rawItems[] = $item;
                }
            }
        }

        $pickupCapabilities = null;
        if (isset($raw['pickupCapabilities']) && is_array($raw['pickupCapabilities'])) {
            /** @var array<string, mixed> $pickupCapabilities */
            $pickupCapabilities = $raw['pickupCapabilities'];
        }

        return new QuotedProduct(
            productName: HydrationHelper::stringField($raw, 'productName'),
            productCode: HydrationHelper::stringField($raw, 'productCode'),
            localProductCode: HydrationHelper::nullableStringField($raw, 'localProductCode'),
            localProductCountryCode: HydrationHelper::nullableStringField($raw, 'localProductCountryCode'),
            networkTypeCode: RateNetworkTypeCode::tryFrom($rawNetworkTypeCode),
            rawNetworkTypeCode: $rawNetworkTypeCode,
            isCustomerAgreement: HydrationHelper::nullableBoolField($raw, 'isCustomerAgreement'),
            weight: $this->hydrateQuotedWeight($raw['weight'] ?? null),
            totalPrices: $this->hydrateTotalPrices($raw['totalPrice'] ?? null),
            totalPriceBreakdowns: $this->hydrateTotalPriceBreakdowns($raw['totalPriceBreakdown'] ?? null),
            detailedPriceBreakdowns: $this->hydrateDetailedPriceBreakdowns($raw['detailedPriceBreakdown'] ?? null),
            pickupCapabilities: $pickupCapabilities,
            deliveryCapabilities: $this->hydrateDeliveryCapability($raw['deliveryCapabilities'] ?? null),
            estimatedDeliveryDate: $this->hydrateEstimatedDeliveryDate($raw['estimatedDeliveryDate'] ?? null),
            rawItems: $rawItems,
        );
    }

    private function hydrateQuotedWeight(mixed $raw): ?QuotedWeight
    {
        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        return new QuotedWeight(
            volumetric: HydrationHelper::floatField($raw, 'volumetric'),
            provided: HydrationHelper::floatField($raw, 'provided'),
            unitOfMeasurement: HydrationHelper::stringField($raw, 'unitOfMeasurement'),
        );
    }

    /**
     * @return list<TotalPrice>
     */
    private function hydrateTotalPrices(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $prices = [];
        foreach ($raw as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            /** @var array<string, mixed> $entry */
            $rawCurrencyType = HydrationHelper::stringField($entry, 'currencyType');
            $prices[] = new TotalPrice(
                currencyType: RateCurrencyType::tryFrom($rawCurrencyType),
                rawCurrencyType: $rawCurrencyType,
                priceCurrency: HydrationHelper::stringField($entry, 'priceCurrency'),
                price: HydrationHelper::floatField($entry, 'price') ?? 0.0,
            );
        }

        return $prices;
    }

    /**
     * @return list<TotalPriceBreakdown>
     */
    private function hydrateTotalPriceBreakdowns(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $rows = [];
        foreach ($raw as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            /** @var array<string, mixed> $entry */
            $rawCurrencyType = HydrationHelper::stringField($entry, 'currencyType');

            $items = [];
            $rawItems = $entry['priceBreakdown'] ?? null;
            if (is_array($rawItems)) {
                foreach ($rawItems as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    /** @var array<string, mixed> $item */
                    $items[] = new PriceBreakdownItem(
                        typeCode: HydrationHelper::stringField($item, 'typeCode'),
                        price: HydrationHelper::floatField($item, 'price') ?? 0.0,
                    );
                }
            }

            $rows[] = new TotalPriceBreakdown(
                currencyType: RateCurrencyType::tryFrom($rawCurrencyType),
                rawCurrencyType: $rawCurrencyType,
                priceCurrency: HydrationHelper::stringField($entry, 'priceCurrency'),
                priceBreakdown: $items,
            );
        }

        return $rows;
    }

    /**
     * @return list<DetailedPriceBreakdown>
     */
    private function hydrateDetailedPriceBreakdowns(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $rows = [];
        foreach ($raw as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            /** @var array<string, mixed> $entry */
            $rawCurrencyType = HydrationHelper::stringField($entry, 'currencyType');

            $details = [];
            $rawBreakdown = $entry['breakdown'] ?? null;
            if (is_array($rawBreakdown)) {
                foreach ($rawBreakdown as $detail) {
                    if (!is_array($detail)) {
                        continue;
                    }
                    /** @var array<string, mixed> $detail */
                    $innerBreakdown = [];
                    $rawInner = $detail['priceBreakdown'] ?? null;
                    if (is_array($rawInner)) {
                        foreach ($rawInner as $row) {
                            if (is_array($row)) {
                                /** @var array<string, mixed> $row */
                                $innerBreakdown[] = $row;
                            }
                        }
                    }

                    $details[] = new BreakdownDetail(
                        name: HydrationHelper::stringField($detail, 'name'),
                        serviceCode: HydrationHelper::nullableStringField($detail, 'serviceCode'),
                        localServiceCode: HydrationHelper::nullableStringField($detail, 'localServiceCode'),
                        typeCode: HydrationHelper::nullableStringField($detail, 'typeCode'),
                        serviceTypeCode: HydrationHelper::nullableStringField($detail, 'serviceTypeCode'),
                        price: HydrationHelper::floatField($detail, 'price') ?? 0.0,
                        priceCurrency: HydrationHelper::nullableStringField($detail, 'priceCurrency'),
                        isCustomerAgreement: HydrationHelper::nullableBoolField($detail, 'isCustomerAgreement'),
                        isMarketedService: HydrationHelper::nullableBoolField($detail, 'isMarketedService'),
                        isBillingServiceIndicator: HydrationHelper::nullableBoolField($detail, 'isBillingServiceIndicator'),
                        priceBreakdown: $innerBreakdown,
                        tariffRateFormula: HydrationHelper::nullableStringField($detail, 'tariffRateFormula'),
                    );
                }
            }

            $rows[] = new DetailedPriceBreakdown(
                currencyType: RateCurrencyType::tryFrom($rawCurrencyType),
                rawCurrencyType: $rawCurrencyType,
                priceCurrency: HydrationHelper::stringField($entry, 'priceCurrency'),
                breakdown: $details,
            );
        }

        return $rows;
    }

    private function hydrateDeliveryCapability(mixed $raw): ?DeliveryCapability
    {
        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        return new DeliveryCapability(
            deliveryTypeCode: HydrationHelper::nullableStringField($raw, 'deliveryTypeCode'),
            estimatedDeliveryDateAndTime: HydrationHelper::nullableStringField($raw, 'estimatedDeliveryDateAndTime'),
            destinationServiceAreaCode: HydrationHelper::nullableStringField($raw, 'destinationServiceAreaCode'),
            destinationFacilityAreaCode: HydrationHelper::nullableStringField($raw, 'destinationFacilityAreaCode'),
            deliveryAdditionalDays: HydrationHelper::floatField($raw, 'deliveryAdditionalDays'),
            deliveryDayOfWeek: HydrationHelper::intField($raw, 'deliveryDayOfWeek'),
            totalTransitDays: HydrationHelper::intField($raw, 'totalTransitDays'),
        );
    }

    private function hydrateEstimatedDeliveryDate(mixed $raw): ?EstimatedDeliveryDate
    {
        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        $rawTypeCode = HydrationHelper::stringField($raw, 'typeCode');

        return new EstimatedDeliveryDate(
            typeCode: EstimatedDeliveryDateTypeCode::tryFrom($rawTypeCode),
            rawTypeCode: $rawTypeCode,
            estimatedDeliveryDate: HydrationHelper::stringField($raw, 'estimatedDeliveryDate'),
        );
    }

}

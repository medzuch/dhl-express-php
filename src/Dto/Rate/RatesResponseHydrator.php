<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateTypeCode;
use Medzuch\DhlExpress\Enum\RateCurrencyType;
use Medzuch\DhlExpress\Enum\RateNetworkTypeCode;

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
                    currentExchangeRate: $this->floatField($entry, 'currentExchangeRate') ?? 0.0,
                    currency: $this->stringField($entry, 'currency'),
                    baseCurrency: $this->stringField($entry, 'baseCurrency'),
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
        $rawNetworkTypeCode = $this->stringField($raw, 'networkTypeCode');

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
            productName: $this->stringField($raw, 'productName'),
            productCode: $this->stringField($raw, 'productCode'),
            localProductCode: $this->nullableStringField($raw, 'localProductCode'),
            localProductCountryCode: $this->nullableStringField($raw, 'localProductCountryCode'),
            networkTypeCode: RateNetworkTypeCode::tryFrom($rawNetworkTypeCode),
            rawNetworkTypeCode: $rawNetworkTypeCode,
            isCustomerAgreement: $this->nullableBoolField($raw, 'isCustomerAgreement'),
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
            volumetric: $this->floatField($raw, 'volumetric'),
            provided: $this->floatField($raw, 'provided'),
            unitOfMeasurement: $this->stringField($raw, 'unitOfMeasurement'),
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
            $rawCurrencyType = $this->stringField($entry, 'currencyType');
            $prices[] = new TotalPrice(
                currencyType: RateCurrencyType::tryFrom($rawCurrencyType),
                rawCurrencyType: $rawCurrencyType,
                priceCurrency: $this->stringField($entry, 'priceCurrency'),
                price: $this->floatField($entry, 'price') ?? 0.0,
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
            $rawCurrencyType = $this->stringField($entry, 'currencyType');

            $items = [];
            $rawItems = $entry['priceBreakdown'] ?? null;
            if (is_array($rawItems)) {
                foreach ($rawItems as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    /** @var array<string, mixed> $item */
                    $items[] = new PriceBreakdownItem(
                        typeCode: $this->stringField($item, 'typeCode'),
                        price: $this->floatField($item, 'price') ?? 0.0,
                    );
                }
            }

            $rows[] = new TotalPriceBreakdown(
                currencyType: RateCurrencyType::tryFrom($rawCurrencyType),
                rawCurrencyType: $rawCurrencyType,
                priceCurrency: $this->stringField($entry, 'priceCurrency'),
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
            $rawCurrencyType = $this->stringField($entry, 'currencyType');

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
                        name: $this->stringField($detail, 'name'),
                        serviceCode: $this->nullableStringField($detail, 'serviceCode'),
                        localServiceCode: $this->nullableStringField($detail, 'localServiceCode'),
                        typeCode: $this->nullableStringField($detail, 'typeCode'),
                        serviceTypeCode: $this->nullableStringField($detail, 'serviceTypeCode'),
                        price: $this->floatField($detail, 'price') ?? 0.0,
                        priceCurrency: $this->nullableStringField($detail, 'priceCurrency'),
                        isCustomerAgreement: $this->nullableBoolField($detail, 'isCustomerAgreement'),
                        isMarketedService: $this->nullableBoolField($detail, 'isMarketedService'),
                        isBillingServiceIndicator: $this->nullableBoolField($detail, 'isBillingServiceIndicator'),
                        priceBreakdown: $innerBreakdown,
                        tariffRateFormula: $this->nullableStringField($detail, 'tariffRateFormula'),
                    );
                }
            }

            $rows[] = new DetailedPriceBreakdown(
                currencyType: RateCurrencyType::tryFrom($rawCurrencyType),
                rawCurrencyType: $rawCurrencyType,
                priceCurrency: $this->stringField($entry, 'priceCurrency'),
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
            deliveryTypeCode: $this->nullableStringField($raw, 'deliveryTypeCode'),
            estimatedDeliveryDateAndTime: $this->nullableStringField($raw, 'estimatedDeliveryDateAndTime'),
            destinationServiceAreaCode: $this->nullableStringField($raw, 'destinationServiceAreaCode'),
            destinationFacilityAreaCode: $this->nullableStringField($raw, 'destinationFacilityAreaCode'),
            deliveryAdditionalDays: $this->floatField($raw, 'deliveryAdditionalDays'),
            deliveryDayOfWeek: $this->intField($raw, 'deliveryDayOfWeek'),
            totalTransitDays: $this->intField($raw, 'totalTransitDays'),
        );
    }

    private function hydrateEstimatedDeliveryDate(mixed $raw): ?EstimatedDeliveryDate
    {
        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        $rawTypeCode = $this->stringField($raw, 'typeCode');

        return new EstimatedDeliveryDate(
            typeCode: EstimatedDeliveryDateTypeCode::tryFrom($rawTypeCode),
            rawTypeCode: $rawTypeCode,
            estimatedDeliveryDate: $this->stringField($raw, 'estimatedDeliveryDate'),
        );
    }

    /**
     * @param array<string, mixed> $source
     */
    private function stringField(array $source, string $key): string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<string, mixed> $source
     */
    private function nullableStringField(array $source, string $key): ?string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function nullableBoolField(array $source, string $key): ?bool
    {
        $value = $source[$key] ?? null;

        return is_bool($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function floatField(array $source, string $key): ?float
    {
        $value = $source[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function intField(array $source, string $key): ?int
    {
        $value = $source[$key] ?? null;

        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return (int) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}

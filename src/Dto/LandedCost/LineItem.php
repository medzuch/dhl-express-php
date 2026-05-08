<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\LandedCost;

use Medzuch\DhlExpress\Enum\LandedCostRateType;
use Medzuch\DhlExpress\Enum\LineItemQuantityType;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * One line item in a landed-cost request.
 *
 * Required fields per the OpenAPI spec: `number`, `quantity`,
 * `unitPrice`, `unitPriceCurrencyCode`, `manufacturerCountry`.
 * Optional fields cover commodity classification, weight,
 * brand/category, and the requested tariff-rate type.
 *
 * Less commonly used arrays (`goodsCharacteristics`,
 * `additionalQuantityDefinitions`) are accepted as raw arrays so the
 * DTO surface stays compact; we'll fan them out into typed children
 * when a concrete consumer surfaces.
 */
final readonly class LineItem
{
    /**
     * @param list<array<string, mixed>> $goodsCharacteristics
     * @param list<array<string, mixed>> $additionalQuantityDefinitions
     */
    public function __construct(
        public int $number,
        public float $quantity,
        public float $unitPrice,
        public CurrencyCode $unitPriceCurrencyCode,
        public CountryCode $manufacturerCountry,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $partNumber = null,
        public ?LineItemQuantityType $quantityType = null,
        public ?float $customsValue = null,
        public ?CurrencyCode $customsValueCurrencyCode = null,
        public ?string $commodityCode = null,
        public ?float $weight = null,
        public ?UnitSystem $weightUnitOfMeasurement = null,
        public ?string $category = null,
        public ?string $brand = null,
        public array $goodsCharacteristics = [],
        public array $additionalQuantityDefinitions = [],
        public ?LandedCostRateType $estimatedTariffRateType = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'number' => $this->number,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'unitPriceCurrencyCode' => $this->unitPriceCurrencyCode->value,
            'manufacturerCountry' => $this->manufacturerCountry->value,
        ];

        if ($this->name !== null) {
            $payload['name'] = $this->name;
        }
        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }
        if ($this->partNumber !== null) {
            $payload['partNumber'] = $this->partNumber;
        }
        if ($this->quantityType !== null) {
            $payload['quantityType'] = $this->quantityType->value;
        }
        if ($this->customsValue !== null) {
            $payload['customsValue'] = $this->customsValue;
        }
        if ($this->customsValueCurrencyCode !== null) {
            $payload['customsValueCurrencyCode'] = $this->customsValueCurrencyCode->value;
        }
        if ($this->commodityCode !== null) {
            $payload['commodityCode'] = $this->commodityCode;
        }
        if ($this->weight !== null) {
            $payload['weight'] = $this->weight;
        }
        if ($this->weightUnitOfMeasurement !== null) {
            $payload['weightUnitOfMeasurement'] = $this->weightUnitOfMeasurement->value;
        }
        if ($this->category !== null) {
            $payload['category'] = $this->category;
        }
        if ($this->brand !== null) {
            $payload['brand'] = $this->brand;
        }
        if ($this->goodsCharacteristics !== []) {
            $payload['goodsCharacteristics'] = $this->goodsCharacteristics;
        }
        if ($this->additionalQuantityDefinitions !== []) {
            $payload['additionalQuantityDefinitions'] = $this->additionalQuantityDefinitions;
        }
        if ($this->estimatedTariffRateType !== null) {
            $payload['estimatedTariffRateType'] = $this->estimatedTariffRateType->value;
        }

        return $payload;
    }
}

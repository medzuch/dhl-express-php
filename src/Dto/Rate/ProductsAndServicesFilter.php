<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

/**
 * Filter combining a product code with optional value-added service
 * filters — DHL applies these together when narrowing the rate
 * response to a specific product/VAS combination.
 *
 * Mirrors `supermodelIoLogisticsExpressRateRequest.productsAndServices[]`.
 */
final readonly class ProductsAndServicesFilter
{
    /**
     * @param list<ValueAddedServiceFilter> $valueAddedServices
     */
    public function __construct(
        public string $productCode,
        public ?string $localProductCode = null,
        public array $valueAddedServices = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = ['productCode' => $this->productCode];

        if ($this->localProductCode !== null) {
            $payload['localProductCode'] = $this->localProductCode;
        }
        if ($this->valueAddedServices !== []) {
            $payload['valueAddedServices'] = array_map(
                static fn (ValueAddedServiceFilter $filter): array => $filter->toArray(),
                $this->valueAddedServices,
            );
        }

        return $payload;
    }
}

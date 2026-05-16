<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * One value-added service attached to a shipment.
 *
 * Mirrors `supermodelIoLogisticsExpressValueAddedServices`.
 *
 * The optional `dangerousGoods` sub-block belongs INSIDE each VAS item
 * (the create-shipment root has no `dangerousGoods` field —
 * `additionalProperties: false`). DHL accepts an array of at most one
 * DG entry per VAS, so the DTO is wrapped in a single-element array on
 * serialisation.
 *
 * Callers should usually let
 * {@see \Medzuch\DhlExpress\Builder\CreateShipmentBuilder::withDangerousGoods()}
 * attach the DG payload to the matching DG-coded VAS automatically;
 * passing it explicitly on the constructor is supported for cases that
 * skip the builder.
 */
final readonly class ValueAddedService
{
    public function __construct(
        public string $serviceCode,
        public ?float $value = null,
        public ?CurrencyCode $currency = null,
        public ?string $method = null,
        public ?DangerousGoods $dangerousGoods = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'serviceCode' => $this->serviceCode,
        ];

        if ($this->value !== null) {
            $payload['value'] = $this->value;
        }
        if ($this->currency !== null) {
            $payload['currency'] = $this->currency->value;
        }
        if ($this->method !== null) {
            $payload['method'] = $this->method;
        }
        if ($this->dangerousGoods !== null) {
            $payload['dangerousGoods'] = [$this->dangerousGoods->toArray()];
        }

        return $payload;
    }

    public function withDangerousGoods(DangerousGoods $dangerousGoods): self
    {
        return new self(
            serviceCode: $this->serviceCode,
            value: $this->value,
            currency: $this->currency,
            method: $this->method,
            dangerousGoods: $dangerousGoods,
        );
    }
}

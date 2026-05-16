<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * One value-added service attached to a pickup-shipment entry.
 *
 * Mirrors `supermodelIoLogisticsExpressValueAddedServicesRates`
 * (spec lines 15291-15300, 17136-17142), distinct from the shipment
 * VAS schema `supermodelIoLogisticsExpressValueAddedServices`:
 *
 * - Pickup VAS exposes `localServiceCode` (country-specific service code)
 * - Pickup VAS has NO `dangerousGoods` sub-block — `additionalProperties: false`
 *   means leaking the shipment-side `dangerousGoods` field would cause DHL
 *   to reject the request.
 *
 * Keep this distinct from
 * {@see \Medzuch\DhlExpress\Dto\Shipment\ValueAddedService} —
 * the two endpoints maintain parallel vocabularies that we mirror.
 */
final readonly class PickupValueAddedService
{
    public function __construct(
        public string $serviceCode,
        public ?string $localServiceCode = null,
        public ?float $value = null,
        public ?CurrencyCode $currency = null,
        public ?string $method = null,
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

        if ($this->localServiceCode !== null) {
            $payload['localServiceCode'] = $this->localServiceCode;
        }
        if ($this->value !== null) {
            $payload['value'] = $this->value;
        }
        if ($this->currency !== null) {
            $payload['currency'] = $this->currency->value;
        }
        if ($this->method !== null) {
            $payload['method'] = $this->method;
        }

        return $payload;
    }
}

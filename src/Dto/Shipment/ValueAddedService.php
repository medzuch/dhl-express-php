<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * One value-added service attached to a shipment.
 *
 * Mirrors `supermodelIoLogisticsExpressValueAddedServices`.
 *
 * Phase 4a keeps `serviceCode` as a free string. The `ServiceCode` enum
 * (~384 codes grouped by `serviceGroupCode`) is deferred to Phase 4b,
 * which will decide whether to ship one large enum or split per group
 * (W=Customs, H=DG, U=Temperature, …).
 *
 * The optional `dangerousGoods` sub-block is deferred to Phase 4b
 * alongside the DG VAS rule (DG service code present ⇒ DG block
 * required).
 */
final readonly class ValueAddedService
{
    public function __construct(
        public string $serviceCode,
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

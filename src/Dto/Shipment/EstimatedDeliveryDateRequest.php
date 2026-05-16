<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateType;

/**
 * EDD (Estimated Delivery Date) request flag on a create-shipment.
 *
 * Mirrors `estimatedDeliveryDate` (spec lines 12652–12674). Only
 * `isRequested` is required; `typeCode` defaults to `QDDC` server-side
 * when omitted.
 */
final readonly class EstimatedDeliveryDateRequest
{
    public function __construct(
        public bool $isRequested,
        public ?EstimatedDeliveryDateType $typeCode = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = ['isRequested' => $this->isRequested];

        if ($this->typeCode !== null) {
            $payload['typeCode'] = $this->typeCode->value;
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Parent (mother) shipment identification on a create-shipment.
 *
 * Mirrors `parentShipment` in MyDHL API 3.4.0, which carries only the
 * parent shipment's product code and package count and rejects any
 * other key (`additionalProperties: false`). The registration-number
 * triplet (typeCode/number/issuerCountryCode) of 3.2.x was removed.
 */
final readonly class ParentShipment
{
    public function __construct(
        public ?string $productCode = null,
        public ?int $packagesCount = null,
    ) {
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->productCode !== null) {
            $payload['productCode'] = $this->productCode;
        }
        if ($this->packagesCount !== null) {
            $payload['packagesCount'] = $this->packagesCount;
        }

        return $payload;
    }
}

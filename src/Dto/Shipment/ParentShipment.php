<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\RegistrationNumberTypeCode;
use Medzuch\DhlExpress\ValueObject\CountryCode;

/**
 * Parent (mother) shipment identification on a create-shipment.
 *
 * Mirrors `parentShipment` (spec lines 12700–12793). The spec lumps a
 * registration-number triplet (typeCode/number/issuerCountryCode) into
 * this object alongside the parent shipment's product code and
 * package count — all fields are optional from the DTO's perspective
 * because the spec's `required:` list refers to the registration
 * triplet only when callers supply any of them.
 */
final readonly class ParentShipment
{
    public function __construct(
        public ?string $productCode = null,
        public ?int $packagesCount = null,
        public ?RegistrationNumberTypeCode $typeCode = null,
        public ?string $number = null,
        public ?CountryCode $issuerCountryCode = null,
    ) {
    }

    /**
     * @return array<string, mixed>
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
        if ($this->typeCode !== null) {
            $payload['typeCode'] = $this->typeCode->value;
        }
        if ($this->number !== null) {
            $payload['number'] = $this->number;
        }
        if ($this->issuerCountryCode !== null) {
            $payload['issuerCountryCode'] = $this->issuerCountryCode->value;
        }

        return $payload;
    }
}

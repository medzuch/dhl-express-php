<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\AdditionalChargeTypeCode;

/**
 * An additional charge line on an export declaration.
 *
 * `value` must be greater than 0 (min 0.001). Up to 5 additional
 * charges are allowed per export declaration.
 */
final readonly class AdditionalCharge
{
    public function __construct(
        public float $value,
        public AdditionalChargeTypeCode $typeCode,
    ) {
    }

    /**
     * @return array<string, float|string>
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'typeCode' => $this->typeCode->value,
        ];
    }
}

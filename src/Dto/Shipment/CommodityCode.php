<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\CommodityCodeType;

/**
 * A single commodity (HS) code attached to an export line item.
 *
 * `value` is the actual HS or commodity code string (2–18 characters).
 * Up to 2 codes are allowed per line item (one inbound, one outbound).
 */
final readonly class CommodityCode
{
    public function __construct(
        public CommodityCodeType $typeCode,
        public string $value,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode->value,
            'value' => $this->value,
        ];
    }
}

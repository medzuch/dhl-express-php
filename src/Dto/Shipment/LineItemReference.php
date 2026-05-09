<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LineItemReferenceTypeCode;

/**
 * A customer reference attached to an export line item.
 *
 * `value` is a free-text reference string (1–35 characters).
 * Up to 100 references are allowed per line item.
 */
final readonly class LineItemReference
{
    public function __construct(
        public LineItemReferenceTypeCode $typeCode,
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

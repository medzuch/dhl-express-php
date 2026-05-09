<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\CustomsDocumentTypeCode;

/**
 * A customs document reference attached to an export line item or
 * the top-level export declaration.
 *
 * `value` is a free-text reference string (1–35 characters).
 * Up to 50 documents are allowed per line item or declaration.
 */
final readonly class LineItemCustomsDocument
{
    public function __construct(
        public CustomsDocumentTypeCode $typeCode,
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

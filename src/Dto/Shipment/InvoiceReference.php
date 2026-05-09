<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\InvoiceReferenceTypeCode;

/**
 * A customer reference attached to an export invoice.
 *
 * `value` is a free-text reference string (1–35 characters).
 * Up to 100 references are allowed per invoice.
 */
final readonly class InvoiceReference
{
    public function __construct(
        public InvoiceReferenceTypeCode $typeCode,
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

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;

/**
 * Quantity sub-object of an export line item.
 *
 * `value` must be between 1 and 1 000 000 000 (inclusive).
 */
final readonly class LineItemQuantity
{
    public function __construct(
        public int $value,
        public LineItemQuantityUnit $unitOfMeasurement,
    ) {
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'unitOfMeasurement' => $this->unitOfMeasurement->value,
        ];
    }
}

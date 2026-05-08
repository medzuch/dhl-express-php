<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateTypeCode;

/**
 * Estimated delivery date returned for a quoted product.
 *
 * Distinct from {@see EstimatedDeliveryDateOption} — this is the
 * response-side companion that carries the resolved date string.
 */
final readonly class EstimatedDeliveryDate
{
    public function __construct(
        public ?EstimatedDeliveryDateTypeCode $typeCode,
        public string $rawTypeCode,
        public string $estimatedDeliveryDate,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateTypeCode;

/**
 * Toggle for whether DHL should compute an estimated delivery date
 * for the quote, plus which variant (`QDDC` or `QDDF`).
 *
 * Mirrors `supermodelIoLogisticsExpressRateRequest.estimatedDeliveryDate`.
 */
final readonly class EstimatedDeliveryDateOption
{
    public function __construct(
        public bool $isRequested,
        public ?EstimatedDeliveryDateTypeCode $typeCode = null,
    ) {
    }

    /**
     * @return array<string, bool|string>
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

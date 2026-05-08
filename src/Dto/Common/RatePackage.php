<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Common;

use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\Weight;

/**
 * One package in a rate, products, or landed-cost request.
 *
 * Mirrors `supermodelIoLogisticsExpressPackageRR`. DHL marks weight
 * as the only required field, so dimensions and the box-type code are
 * optional. The unit attached to {@see Weight}/{@see Dimensions} must
 * line up with the request's shipment-level `unitOfMeasurement`; the
 * builder enforces that cross-field rule.
 */
final readonly class RatePackage
{
    public function __construct(
        public Weight $weight,
        public ?Dimensions $dimensions = null,
        public ?PackageTypeCode $typeCode = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'weight' => $this->weight->value,
        ];

        if ($this->typeCode !== null) {
            $payload['typeCode'] = $this->typeCode->value;
        }
        if ($this->dimensions !== null) {
            $payload['dimensions'] = [
                'length' => $this->dimensions->length,
                'width' => $this->dimensions->width,
                'height' => $this->dimensions->height,
            ];
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\Weight;

/**
 * One package within a pickup `shipmentDetails` entry.
 *
 * Mirrors `supermodelIoLogisticsExpressPackageRR`. The unit of
 * measurement is declared at the `shipmentDetails` level; the
 * `Weight` and `Dimensions` VOs carry units for consistency
 * with the rest of the codebase — the builder validates alignment.
 */
final readonly class PickupPackage
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
        $payload = ['weight' => $this->weight->value];

        if ($this->dimensions !== null) {
            $payload['dimensions'] = [
                'length' => $this->dimensions->length,
                'width' => $this->dimensions->width,
                'height' => $this->dimensions->height,
            ];
        }

        if ($this->typeCode !== null) {
            $payload['typeCode'] = $this->typeCode->value;
        }

        return $payload;
    }
}

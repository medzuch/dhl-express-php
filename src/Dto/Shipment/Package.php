<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\Weight;

/**
 * One package in a `POST /shipments` request.
 *
 * Mirrors `supermodelIoLogisticsExpressPackage`. Distinct from
 * {@see \Medzuch\DhlExpress\Dto\Common\RatePackage} — shipment-side
 * packages can carry customer references (printed on the label) and a
 * referenceNumber (piece serial), neither of which the rates endpoint
 * needs.
 *
 * The unit attached to {@see Weight}/{@see Dimensions} must match the
 * shipment-level unit system; the {@see
 * \Medzuch\DhlExpress\Builder\CreateShipmentBuilder} enforces that
 * cross-field rule across all packages.
 */
final readonly class Package
{
    /**
     * @param list<PackageReference> $customerReferences
     */
    public function __construct(
        public Weight $weight,
        public ?Dimensions $dimensions = null,
        public ?PackageTypeCode $typeCode = null,
        public ?string $description = null,
        public ?int $referenceNumber = null,
        public array $customerReferences = [],
        public ?string $labelDescription = null,
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
        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }
        if ($this->referenceNumber !== null) {
            $payload['referenceNumber'] = $this->referenceNumber;
        }
        if ($this->customerReferences !== []) {
            $payload['customerReferences'] = array_map(
                static fn (PackageReference $reference): array => $reference->toArray(),
                $this->customerReferences,
            );
        }
        if ($this->labelDescription !== null) {
            $payload['labelDescription'] = $this->labelDescription;
        }

        return $payload;
    }
}

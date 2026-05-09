<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;

/**
 * One package in a `PATCH /shipments/{id}/add-piece` request.
 *
 * Mirrors `supermodelIoLogisticsExpressPackageAddPiece`.
 * Weight is the only required field; all other fields are optional.
 */
final readonly class AddPiecePackage
{
    /**
     * @param list<PackageReference> $customerReferences
     */
    public function __construct(
        public float $weight,
        public ?PackageTypeCode $typeCode = null,
        public ?Dimensions $dimensions = null,
        public array $customerReferences = [],
        public ?string $description = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'weight' => $this->weight,
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
        if ($this->customerReferences !== []) {
            $payload['customerReferences'] = array_map(
                static fn (PackageReference $reference): array => $reference->toArray(),
                $this->customerReferences,
            );
        }
        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        return $payload;
    }
}

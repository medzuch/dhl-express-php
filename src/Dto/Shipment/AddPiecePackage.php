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
     * @param list<Identifier>       $identifiers   max 3 entries per spec
     * @param list<LabelBarcode>     $labelBarcodes max 2 entries per spec
     * @param list<LabelText>        $labelText     max 6 entries per spec
     */
    public function __construct(
        public float $weight,
        public ?PackageTypeCode $typeCode = null,
        public ?Dimensions $dimensions = null,
        public array $customerReferences = [],
        public ?string $description = null,
        public array $identifiers = [],
        public array $labelBarcodes = [],
        public array $labelText = [],
        public ?string $labelDescription = null,
        public ?int $referenceNumber = null,
        public ?bool $isThisTheLastPackageAdded = null,
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
        if ($this->identifiers !== []) {
            $payload['identifiers'] = array_map(
                static fn (Identifier $identifier): array => $identifier->toArray(),
                $this->identifiers,
            );
        }
        if ($this->labelBarcodes !== []) {
            $payload['labelBarcodes'] = array_map(
                static fn (LabelBarcode $barcode): array => $barcode->toArray(),
                $this->labelBarcodes,
            );
        }
        if ($this->labelText !== []) {
            $payload['labelText'] = array_map(
                static fn (LabelText $text): array => $text->toArray(),
                $this->labelText,
            );
        }
        if ($this->labelDescription !== null) {
            $payload['labelDescription'] = $this->labelDescription;
        }
        if ($this->referenceNumber !== null) {
            $payload['referenceNumber'] = $this->referenceNumber;
        }
        if ($this->isThisTheLastPackageAdded !== null) {
            $payload['isThisTheLastPackageAdded'] = $this->isThisTheLastPackageAdded;
        }

        return $payload;
    }
}

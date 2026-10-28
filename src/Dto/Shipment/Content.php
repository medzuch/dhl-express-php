<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\UnitSystem;

/**
 * The `content` block on a create-shipment request.
 *
 * Mirrors the inline `content` object in
 * `supermodelIoLogisticsExpressCreateShipmentRequest` (MyDHL API
 * 3.4.0). The spec marks `packages`, `isCustomsDeclarable`,
 * `description` and `unitOfMeasurement` as required at the content
 * level; `incoterm` became optional in 3.4.0 and, when set, acts as
 * the default for export declarations without their own.
 *
 * Export declarations serialize as the `exportDeclarations` list
 * (3.4.0 replaced the single `exportDeclaration` object). The
 * {@see \Medzuch\DhlExpress\Builder\CreateShipmentBuilder} enforces
 * at least one declaration when `isCustomsDeclarable=true`.
 *
 * `dimensionUnitOfMeasurement` lets package dimensions use a different
 * unit system from the weights (`unitOfMeasurement`).
 */
final readonly class Content
{
    /**
     * @param list<Package>           $packages
     * @param list<ExportDeclaration> $exportDeclarations
     */
    public function __construct(
        public array $packages,
        public bool $isCustomsDeclarable,
        public string $description,
        public ?Incoterm $incoterm,
        public UnitSystem $unitOfMeasurement,
        public ?float $declaredValue = null,
        public ?string $declaredValueCurrency = null,
        public array $exportDeclarations = [],
        public ?bool $areMorePackagesToBeAddedLater = null,
        public ?string $USFilingTypeValue = null,
        public ?UnitSystem $dimensionUnitOfMeasurement = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'packages' => array_map(
                static fn (Package $package): array => $package->toArray(),
                $this->packages,
            ),
            'isCustomsDeclarable' => $this->isCustomsDeclarable,
            'description' => $this->description,
        ];

        if ($this->incoterm !== null) {
            $payload['incoterm'] = $this->incoterm->value;
        }
        $payload['unitOfMeasurement'] = $this->unitOfMeasurement->value;
        if ($this->dimensionUnitOfMeasurement !== null) {
            $payload['dimensionUnitOfMeasurement'] = $this->dimensionUnitOfMeasurement->value;
        }

        if ($this->declaredValue !== null) {
            $payload['declaredValue'] = $this->declaredValue;
        }
        if ($this->declaredValueCurrency !== null) {
            $payload['declaredValueCurrency'] = $this->declaredValueCurrency;
        }
        if ($this->exportDeclarations !== []) {
            $payload['exportDeclarations'] = array_map(
                static fn (ExportDeclaration $declaration): array => $declaration->toArray(),
                $this->exportDeclarations,
            );
        }
        if ($this->areMorePackagesToBeAddedLater !== null) {
            $payload['areMorePackagesToBeAddedLater'] = $this->areMorePackagesToBeAddedLater;
        }
        if ($this->USFilingTypeValue !== null) {
            $payload['USFilingTypeValue'] = $this->USFilingTypeValue;
        }

        return $payload;
    }
}

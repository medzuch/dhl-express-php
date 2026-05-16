<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\UnitSystem;

/**
 * The `content` block on a create-shipment request.
 *
 * Mirrors the inline `content` object in
 * `supermodelIoLogisticsExpressCreateShipmentRequest`. The DHL spec
 * marks `packages`, `isCustomsDeclarable`, `description`, `incoterm`,
 * and `unitOfMeasurement` as required at the content level.
 *
 * The {@see \Medzuch\DhlExpress\Builder\CreateShipmentBuilder} enforces
 * that `exportDeclaration` is provided when `isCustomsDeclarable=true`.
 */
final readonly class Content
{
    /**
     * @param list<Package> $packages
     */
    public function __construct(
        public array $packages,
        public bool $isCustomsDeclarable,
        public string $description,
        public Incoterm $incoterm,
        public UnitSystem $unitOfMeasurement,
        public ?float $declaredValue = null,
        public ?string $declaredValueCurrency = null,
        public ?ExportDeclaration $exportDeclaration = null,
        public ?bool $areMorePackagesToBeAddedLater = null,
        public ?string $USFilingTypeValue = null,
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
            'incoterm' => $this->incoterm->value,
            'unitOfMeasurement' => $this->unitOfMeasurement->value,
        ];

        if ($this->declaredValue !== null) {
            $payload['declaredValue'] = $this->declaredValue;
        }
        if ($this->declaredValueCurrency !== null) {
            $payload['declaredValueCurrency'] = $this->declaredValueCurrency;
        }
        if ($this->exportDeclaration !== null) {
            $payload['exportDeclaration'] = $this->exportDeclaration->toArray();
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

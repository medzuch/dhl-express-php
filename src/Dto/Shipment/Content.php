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
 * Phase 4a holds the package list, customs flag, description,
 * unitOfMeasurement, and optional incoterm. The `exportDeclaration`
 * sub-block is deferred to Phase 4b along with the customs party types
 * and `Invoice` / `LineItem` DTOs that populate it.
 *
 * The {@see \Medzuch\DhlExpress\Builder\CreateShipmentBuilder} rejects
 * `isCustomsDeclarable=true` in Phase 4a since no `exportDeclaration`
 * setter is exposed — see the customs guardrail rule in `build()`.
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
        public UnitSystem $unitOfMeasurement,
        public ?Incoterm $incoterm = null,
        public ?float $declaredValue = null,
        public ?string $declaredValueCurrency = null,
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
            'unitOfMeasurement' => $this->unitOfMeasurement->value,
        ];

        if ($this->incoterm !== null) {
            $payload['incoterm'] = $this->incoterm->value;
        }
        if ($this->declaredValue !== null) {
            $payload['declaredValue'] = $this->declaredValue;
        }
        if ($this->declaredValueCurrency !== null) {
            $payload['declaredValueCurrency'] = $this->declaredValueCurrency;
        }

        return $payload;
    }
}

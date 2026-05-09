<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ValueAddedService;
use Medzuch\DhlExpress\Enum\UnitSystem;

/**
 * One shipment entry within a pickup request.
 *
 * Mirrors the `shipmentDetails` array item in
 * `supermodelIoLogisticsExpressPickupRequest`. A single pickup request
 * can cover 1–999 shipments (e.g. a consolidated depot pickup).
 *
 * @property list<PickupPackage> $packages
 * @property list<Account> $accounts
 * @property list<ValueAddedService> $valueAddedServices
 */
final readonly class PickupShipmentDetails
{
    /**
     * @param list<PickupPackage>       $packages
     * @param list<Account>             $accounts
     * @param list<ValueAddedService>   $valueAddedServices
     */
    public function __construct(
        public string $productCode,
        public bool $isCustomsDeclarable,
        public UnitSystem $unitOfMeasurement,
        public array $packages,
        public ?string $localProductCode = null,
        public array $accounts = [],
        public array $valueAddedServices = [],
        public ?float $declaredValue = null,
        public ?string $declaredValueCurrency = null,
        public ?string $shipmentTrackingNumber = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'productCode' => $this->productCode,
            'isCustomsDeclarable' => $this->isCustomsDeclarable,
            'unitOfMeasurement' => $this->unitOfMeasurement->value,
            'packages' => array_map(
                static fn (PickupPackage $p): array => $p->toArray(),
                $this->packages,
            ),
        ];

        if ($this->localProductCode !== null) {
            $payload['localProductCode'] = $this->localProductCode;
        }
        if ($this->accounts !== []) {
            $payload['accounts'] = array_map(
                static fn (Account $a): array => $a->toArray(),
                $this->accounts,
            );
        }
        if ($this->valueAddedServices !== []) {
            $payload['valueAddedServices'] = array_map(
                static fn (ValueAddedService $v): array => $v->toArray(),
                $this->valueAddedServices,
            );
        }
        if ($this->declaredValue !== null) {
            $payload['declaredValue'] = $this->declaredValue;
        }
        if ($this->declaredValueCurrency !== null) {
            $payload['declaredValueCurrency'] = $this->declaredValueCurrency;
        }
        if ($this->shipmentTrackingNumber !== null) {
            $payload['shipmentTrackingNumber'] = $this->shipmentTrackingNumber;
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Response from POST `/shipments`.
 *
 * Mirrors `supermodelIoLogisticsExpressCreateShipmentResponse`.
 *
 * In `validateDataOnly=true` mode DHL returns the same response shape
 * with limited fields populated — typically `shipmentTrackingNumber`
 * is empty and only validation diagnostics flow back. Consumers should
 * treat empty strings on the optional fields as legitimate.
 */
final readonly class CreateShipmentResponse
{
    /**
     * @param list<PackageResult>    $packages
     * @param list<ShipmentDocument> $documents
     */
    public function __construct(
        public string $shipmentTrackingNumber,
        public array $packages = [],
        public array $documents = [],
        public ?string $cancelPickupUrl = null,
        public ?string $trackingUrl = null,
        public ?string $dispatchConfirmationNumber = null,
    ) {
    }
}

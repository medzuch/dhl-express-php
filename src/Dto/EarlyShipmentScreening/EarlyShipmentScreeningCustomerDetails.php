<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\EarlyShipmentScreening;

use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;

/**
 * `customerDetails` block for an early-shipment-screening request.
 *
 * Mirrors the `customerDetails` shape of
 * `supermodelIoLogisticsExpressEarlyShipmentScreeningRequest`. The
 * shipper and receiver pair is required; importer and exporter are
 * optional. Each role reuses {@see ContactAddress} (postal address
 * + contact information) — the same composite used by the
 * create-shipment customerDetails block.
 */
final readonly class EarlyShipmentScreeningCustomerDetails
{
    public function __construct(
        public ContactAddress $shipperDetails,
        public ContactAddress $receiverDetails,
        public ?ContactAddress $importerDetails = null,
        public ?ContactAddress $exporterDetails = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'shipperDetails' => $this->shipperDetails->toArray(),
            'receiverDetails' => $this->receiverDetails->toArray(),
        ];

        if ($this->importerDetails !== null) {
            $payload['importerDetails'] = $this->importerDetails->toArray();
        }
        if ($this->exporterDetails !== null) {
            $payload['exporterDetails'] = $this->exporterDetails->toArray();
        }

        return $payload;
    }
}

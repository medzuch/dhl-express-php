<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * The shipper / receiver pair under `customerDetails` in a
 * create-shipment request.
 *
 * Mirrors the inline `customerDetails` object in
 * `supermodelIoLogisticsExpressCreateShipmentRequest`. Distinct from
 * {@see \Medzuch\DhlExpress\Dto\Common\CustomerDetails} (the rates
 * one): shipment customer details require {@see ContactAddress} (the
 * full address + contact composite), not just an address.
 *
 * Phase 4a only models the two required parties. The optional party
 * types (payer, buyer, importer, exporter, seller, manufacturer,
 * ultimateConsignee, broker, pickupRequestor) defer to Phase 4b.
 */
final readonly class CustomerDetails
{
    public function __construct(
        public ContactAddress $shipperDetails,
        public ContactAddress $receiverDetails,
    ) {
    }

    /**
     * @return array<string, array<string, array<string, string>>>
     */
    public function toArray(): array
    {
        return [
            'shipperDetails' => $this->shipperDetails->toArray(),
            'receiverDetails' => $this->receiverDetails->toArray(),
        ];
    }
}

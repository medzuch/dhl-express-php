<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Customer parties under `customerDetails` in a create-shipment
 * request.
 *
 * Mirrors the inline `customerDetails` object in
 * `supermodelIoLogisticsExpressCreateShipmentRequest`. Distinct from
 * {@see \Medzuch\DhlExpress\Dto\Common\CustomerDetails} (the rates
 * one): shipment customer details require {@see ContactAddress} (the
 * full address + contact composite), not just an address.
 *
 * Required parties: `shipperDetails`, `receiverDetails`.
 *
 * Optional parties — all wrapped in {@see ShipmentParty} so they can
 * carry `registrationNumbers`, `bankDetails`, and `typeCode`:
 *   - `buyerDetails`, `importerDetails`, `exporterDetails`,
 *     `sellerDetails`, `payerDetails`, `manufacturerDetails`,
 *     `ultimateConsigneeDetails`, `brokerDetails`.
 */
final readonly class CustomerDetails
{
    public function __construct(
        public ContactAddress $shipperDetails,
        public ContactAddress $receiverDetails,
        public ?ShipmentParty $buyerDetails = null,
        public ?ShipmentParty $importerDetails = null,
        public ?ShipmentParty $exporterDetails = null,
        public ?ShipmentParty $sellerDetails = null,
        public ?ShipmentParty $payerDetails = null,
        public ?ShipmentParty $manufacturerDetails = null,
        public ?ShipmentParty $ultimateConsigneeDetails = null,
        public ?ShipmentParty $brokerDetails = null,
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

        foreach (
            [
                'buyerDetails' => $this->buyerDetails,
                'importerDetails' => $this->importerDetails,
                'exporterDetails' => $this->exporterDetails,
                'sellerDetails' => $this->sellerDetails,
                'payerDetails' => $this->payerDetails,
                'manufacturerDetails' => $this->manufacturerDetails,
                'ultimateConsigneeDetails' => $this->ultimateConsigneeDetails,
                'brokerDetails' => $this->brokerDetails,
            ] as $key => $party
        ) {
            if ($party !== null) {
                $payload[$key] = $party->toArray();
            }
        }

        return $payload;
    }
}

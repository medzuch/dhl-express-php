<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;

/**
 * Customer party block for a pickup request.
 *
 * Mirrors the inline `customerDetails` schema in
 * `supermodelIoLogisticsExpressPickupRequest`. Only `shipperDetails`
 * is required; the other parties are optional.
 *
 * `bookingRequestorDetails` is omitted — its postalAddress is
 * optional in the spec, making it structurally incompatible with
 * {@see ContactAddress} (which requires address fields). Add a
 * dedicated DTO if a caller needs it.
 */
final readonly class PickupCustomerDetails
{
    public function __construct(
        public ContactAddress $shipperDetails,
        public ?ContactAddress $receiverDetails = null,
        public ?ContactAddress $pickupDetails = null,
    ) {
    }

    /**
     * @return array<string, array<string, array<string, string>>>
     */
    public function toArray(): array
    {
        $payload = ['shipperDetails' => $this->shipperDetails->toArray()];

        if ($this->receiverDetails !== null) {
            $payload['receiverDetails'] = $this->receiverDetails->toArray();
        }
        if ($this->pickupDetails !== null) {
            $payload['pickupDetails'] = $this->pickupDetails->toArray();
        }

        return $payload;
    }
}

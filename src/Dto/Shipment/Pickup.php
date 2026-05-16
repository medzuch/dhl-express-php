<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Pickup\PickupSpecialInstruction;

/**
 * Pickup configuration for a create-shipment request.
 *
 * Mirrors the inline `pickup` object in
 * `supermodelIoLogisticsExpressCreateShipmentRequest` (spec lines
 * 10669–10786). `isRequested` is the only required field.
 *
 * `pickupDetails` and `pickupRequestorDetails` are customer-party
 * slots; they reuse {@see ShipmentParty} since the spec gives them
 * the same address + contactInformation + optional
 * registrationNumbers/bankDetails/typeCode shape used elsewhere in
 * the request.
 *
 * `specialInstructions` reuses the standalone
 * {@see PickupSpecialInstruction} — the inline `specialInstructions[]`
 * here has the same `{value, typeCode?}` shape as the dedicated
 * `/pickups` endpoint.
 */
final readonly class Pickup
{
    /**
     * @param list<PickupSpecialInstruction> $specialInstructions max 4 per spec
     */
    public function __construct(
        public bool $isRequested,
        public ?string $closeTime = null,
        public ?string $location = null,
        public array $specialInstructions = [],
        public ?ShipmentParty $pickupDetails = null,
        public ?ShipmentParty $pickupRequestorDetails = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'isRequested' => $this->isRequested,
        ];

        if ($this->closeTime !== null) {
            $payload['closeTime'] = $this->closeTime;
        }
        if ($this->location !== null) {
            $payload['location'] = $this->location;
        }
        if ($this->specialInstructions !== []) {
            $payload['specialInstructions'] = array_map(
                static fn (PickupSpecialInstruction $instr): array => $instr->toArray(),
                $this->specialInstructions,
            );
        }
        if ($this->pickupDetails !== null) {
            $payload['pickupDetails'] = $this->pickupDetails->toArray();
        }
        if ($this->pickupRequestorDetails !== null) {
            $payload['pickupRequestorDetails'] = $this->pickupRequestorDetails->toArray();
        }

        return $payload;
    }
}

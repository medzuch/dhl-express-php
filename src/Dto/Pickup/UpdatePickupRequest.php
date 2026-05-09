<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Enum\PickupLocationType;

/**
 * Top-level request DTO for `PATCH /pickups/{dispatchConfirmationNumber}`.
 *
 * Mirrors `supermodelIoLogisticsExpressUpdatePickupRequest`.
 * `dispatchConfirmationNumber` appears in both the path and the body
 * per the DHL spec — `PickupApi::update()` uses it for both.
 *
 * @property list<Account>                   $accounts
 * @property list<PickupShipmentDetails>     $shipmentDetails
 * @property list<PickupSpecialInstruction>  $specialInstructions
 */
final readonly class UpdatePickupRequest
{
    /**
     * @param list<Account>                  $accounts
     * @param list<PickupShipmentDetails>    $shipmentDetails
     * @param list<PickupSpecialInstruction> $specialInstructions
     */
    public function __construct(
        public string $dispatchConfirmationNumber,
        public string $originalShipperAccountNumber,
        public DateTimeImmutable $plannedPickupDateAndTime,
        public array $accounts,
        public PickupCustomerDetails $customerDetails,
        public ?string $closeTime = null,
        public ?string $location = null,
        public ?PickupLocationType $locationType = null,
        public ?string $remark = null,
        public array $specialInstructions = [],
        public array $shipmentDetails = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'dispatchConfirmationNumber' => $this->dispatchConfirmationNumber,
            'originalShipperAccountNumber' => $this->originalShipperAccountNumber,
            'plannedPickupDateAndTime' => $this->plannedPickupDateAndTime->format('Y-m-d\TH:i:s\G\M\TP'),
            'accounts' => array_map(
                static fn (Account $a): array => $a->toArray(),
                $this->accounts,
            ),
            'customerDetails' => $this->customerDetails->toArray(),
        ];

        if ($this->closeTime !== null) {
            $payload['closeTime'] = $this->closeTime;
        }
        if ($this->location !== null) {
            $payload['location'] = $this->location;
        }
        if ($this->locationType !== null) {
            $payload['locationType'] = $this->locationType->value;
        }
        if ($this->remark !== null) {
            $payload['remark'] = $this->remark;
        }
        if ($this->specialInstructions !== []) {
            $payload['specialInstructions'] = array_map(
                static fn (PickupSpecialInstruction $i): array => $i->toArray(),
                $this->specialInstructions,
            );
        }
        if ($this->shipmentDetails !== []) {
            $payload['shipmentDetails'] = array_map(
                static fn (PickupShipmentDetails $s): array => $s->toArray(),
                $this->shipmentDetails,
            );
        }

        return $payload;
    }
}

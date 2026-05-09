<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Enum\PickupLocationType;

/**
 * Top-level request DTO for `POST /pickups`.
 *
 * Mirrors `supermodelIoLogisticsExpressPickupRequest`.
 *
 * `plannedPickupDateAndTime` uses DHL's non-standard timestamp format
 * `YYYY-MM-DDTHH:MM:SSGMT±HH:MM` (e.g. `2026-06-02T13:00:00GMT+01:00`).
 * Pass a `DateTimeImmutable` with the correct timezone set; `toArray()`
 * serialises it accordingly.
 *
 * @property list<Account>                   $accounts
 * @property list<PickupShipmentDetails>     $shipmentDetails
 * @property list<PickupSpecialInstruction>  $specialInstructions
 */
final readonly class CreatePickupRequest
{
    /**
     * @param list<Account>                  $accounts
     * @param list<PickupShipmentDetails>    $shipmentDetails
     * @param list<PickupSpecialInstruction> $specialInstructions
     */
    public function __construct(
        public DateTimeImmutable $plannedPickupDateAndTime,
        public array $accounts,
        public PickupCustomerDetails $customerDetails,
        public array $shipmentDetails,
        public ?string $closeTime = null,
        public ?string $location = null,
        public ?PickupLocationType $locationType = null,
        public ?string $remark = null,
        public array $specialInstructions = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'plannedPickupDateAndTime' => $this->plannedPickupDateAndTime->format('Y-m-d\TH:i:s\G\M\TP'),
            'accounts' => array_map(
                static fn (Account $a): array => $a->toArray(),
                $this->accounts,
            ),
            'customerDetails' => $this->customerDetails->toArray(),
            'shipmentDetails' => array_map(
                static fn (PickupShipmentDetails $s): array => $s->toArray(),
                $this->shipmentDetails,
            ),
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

        return $payload;
    }
}

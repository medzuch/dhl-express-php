<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Builder;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupRequest;
use Medzuch\DhlExpress\Dto\Pickup\PickupCustomerDetails;
use Medzuch\DhlExpress\Dto\Pickup\PickupShipmentDetails;
use Medzuch\DhlExpress\Dto\Pickup\PickupSpecialInstruction;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Enum\PickupLocationType;
use Medzuch\DhlExpress\Exception\InvalidRequestException;

/**
 * Fluent builder for {@see CreatePickupRequest}.
 *
 * Cross-field rules enforced at {@see self::build()} time (errors
 * accumulate and are thrown together as a single
 * {@see InvalidRequestException}):
 *
 * - Required fields: `shipperDetails`, `plannedPickupDateAndTime`,
 *   at least one account, at least one shipment-details entry.
 * - Accounts: 1–5 (DHL pickup spec max).
 * - Shipment details: 1–999.
 * - `plannedPickupDateAndTime` must be in the future.
 * - `plannedPickupDateAndTime` must not be more than 10 days ahead
 *   (DHL API constraint per the OpenAPI spec).
 * - When `closeTime` is provided, the time portion of
 *   `plannedPickupDateAndTime` must be strictly before `closeTime`
 *   (a courier needs to arrive and collect before the premises close).
 */
final class PickupRequestBuilder
{
    private ?ContactAddress $shipper = null;
    private ?DateTimeImmutable $plannedPickupDateAndTime = null;
    private ?string $closeTime = null;
    private ?string $location = null;
    private ?PickupLocationType $locationType = null;
    private ?string $remark = null;
    /** @var list<Account> */
    private array $accounts = [];
    /** @var list<PickupShipmentDetails> */
    private array $shipmentDetails = [];
    /** @var list<PickupSpecialInstruction> */
    private array $specialInstructions = [];

    public function withShipper(ContactAddress $address): self
    {
        $this->shipper = $address;

        return $this;
    }

    public function withPlannedPickupDateTime(DateTimeImmutable $dateTime): self
    {
        $this->plannedPickupDateAndTime = $dateTime;

        return $this;
    }

    public function withCloseTime(string $closeTime): self
    {
        $this->closeTime = $closeTime;

        return $this;
    }

    public function withLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function withLocationType(PickupLocationType $locationType): self
    {
        $this->locationType = $locationType;

        return $this;
    }

    public function withRemark(string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function withAccount(Account $account): self
    {
        $this->accounts[] = $account;

        return $this;
    }

    public function withShipmentDetails(PickupShipmentDetails $details): self
    {
        $this->shipmentDetails[] = $details;

        return $this;
    }

    public function withSpecialInstruction(PickupSpecialInstruction $instruction): self
    {
        $this->specialInstructions[] = $instruction;

        return $this;
    }

    /**
     * @throws InvalidRequestException when one or more cross-field rules fail
     */
    public function build(): CreatePickupRequest
    {
        $errors = [];
        $now = new DateTimeImmutable();

        if ($this->shipper === null) {
            $errors[] = [
                'field' => 'customerDetails.shipperDetails',
                'message' => 'shipperDetails is required',
            ];
        }

        $plannedPickupDateAndTime = $this->plannedPickupDateAndTime;

        if ($plannedPickupDateAndTime === null) {
            $errors[] = [
                'field' => 'plannedPickupDateAndTime',
                'message' => 'plannedPickupDateAndTime is required',
            ];
        } else {
            if ($plannedPickupDateAndTime <= $now) {
                $errors[] = [
                    'field' => 'plannedPickupDateAndTime',
                    'message' => 'plannedPickupDateAndTime must be in the future',
                ];
            } elseif ($plannedPickupDateAndTime > $now->modify('+10 days')) {
                $errors[] = [
                    'field' => 'plannedPickupDateAndTime',
                    'message' => 'plannedPickupDateAndTime must not be more than 10 days in the future',
                ];
            } elseif ($this->closeTime !== null) {
                $pickupHHMM = (int) $plannedPickupDateAndTime->setTimezone(new \DateTimeZone('UTC'))->format('Hi');
                [$closeH, $closeM] = explode(':', $this->closeTime);
                $closeHHMM = (int) $closeH * 100 + (int) $closeM;

                if ($pickupHHMM >= $closeHHMM) {
                    $errors[] = [
                        'field' => 'closeTime',
                        'message' => sprintf(
                            'pickup time (%s) must be before closeTime (%s)',
                            $plannedPickupDateAndTime->format('H:i'),
                            $this->closeTime,
                        ),
                    ];
                }
            }
        }

        $accountCount = count($this->accounts);
        if ($accountCount < 1) {
            $errors[] = ['field' => 'accounts', 'message' => 'at least one account is required'];
        } elseif ($accountCount > 5) {
            $errors[] = [
                'field' => 'accounts',
                'message' => sprintf('at most 5 accounts allowed; got %d', $accountCount),
            ];
        }

        $detailsCount = count($this->shipmentDetails);
        if ($detailsCount < 1) {
            $errors[] = ['field' => 'shipmentDetails', 'message' => 'at least one shipmentDetails entry is required'];
        } elseif ($detailsCount > 999) {
            $errors[] = [
                'field' => 'shipmentDetails',
                'message' => sprintf('at most 999 shipmentDetails entries allowed; got %d', $detailsCount),
            ];
        }

        if ($errors !== []) {
            throw new InvalidRequestException($errors);
        }

        // PHPStan narrowing — $plannedPickupDateAndTime is a local so the
        // analyser does not narrow it through the error-flow throw above.
        // $this->shipper IS narrowed automatically, so the null check is on
        // the local variable only.
        if ($plannedPickupDateAndTime === null) {
            throw new InvalidRequestException([
                ['field' => 'build', 'message' => 'internal: required field missing after validation'],
            ]);
        }

        return new CreatePickupRequest(
            plannedPickupDateAndTime: $plannedPickupDateAndTime,
            accounts: $this->accounts,
            customerDetails: new PickupCustomerDetails(shipperDetails: $this->shipper),
            shipmentDetails: $this->shipmentDetails,
            closeTime: $this->closeTime,
            location: $this->location,
            locationType: $this->locationType,
            remark: $this->remark,
            specialInstructions: $this->specialInstructions,
        );
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\EarlyShipmentScreening;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;

/**
 * Request body for `POST /early-shipment-screening`.
 *
 * Used to run Denied Party screening on Breakbulk ('baby') shipments
 * before a full create-shipment call. DHL returns a screening
 * outcome — typically RED/GREEN — that indicates whether the shipment
 * needs further compliance investigation.
 *
 * Mirrors `supermodelIoLogisticsExpressEarlyShipmentScreeningRequest`.
 * The spec requires `plannedShippingDateAndTime`, `productCode`,
 * `customerDetails`, and at least one `accounts` entry. At least one
 * of {@see $customerReferences} or {@see $identifiers} must also be
 * provided per the spec description; either carries the unique
 * reference DHL uses to correlate the screening result with a later
 * shipment.
 *
 * Cross-field validation of the customerReferences-or-identifiers
 * requirement is left to DHL's server: at the time of writing both
 * fields are encoded as `minItems: 0` in the schema, so we don't
 * pre-empt the server's authoritative rule.
 */
final readonly class EarlyShipmentScreeningRequest
{
    /**
     * @param list<Account>                                       $accounts
     * @param list<EarlyShipmentScreeningCustomerReference>       $customerReferences
     * @param list<EarlyShipmentScreeningIdentifier>              $identifiers
     */
    public function __construct(
        public DateTimeImmutable $plannedShippingDateAndTime,
        public string $productCode,
        public EarlyShipmentScreeningCustomerDetails $customerDetails,
        public array $accounts,
        public array $customerReferences = [],
        public array $identifiers = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'plannedShippingDateAndTime' => $this->plannedShippingDateAndTime->format('Y-m-d\TH:i:s'),
            'productCode' => $this->productCode,
            'customerDetails' => $this->customerDetails->toArray(),
            'accounts' => array_map(
                static fn (Account $account): array => $account->toArray(),
                $this->accounts,
            ),
        ];

        if ($this->customerReferences !== []) {
            $payload['customerReferences'] = array_map(
                static fn (EarlyShipmentScreeningCustomerReference $ref): array => $ref->toArray(),
                $this->customerReferences,
            );
        }

        if ($this->identifiers !== []) {
            $payload['identifiers'] = array_map(
                static fn (EarlyShipmentScreeningIdentifier $identifier): array => $identifier->toArray(),
                $this->identifiers,
            );
        }

        return $payload;
    }
}

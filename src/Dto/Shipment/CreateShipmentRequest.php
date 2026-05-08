<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;

/**
 * Top-level POST `/shipments` request body.
 *
 * Mirrors `supermodelIoLogisticsExpressCreateShipmentRequest`. The DHL
 * spec marks `plannedShippingDateAndTime`, `pickup`, `productCode`,
 * `accounts`, `customerDetails`, and `content` as required — those are
 * non-optional constructor parameters.
 *
 * Construct one of these directly, or — for cross-field validation
 * (account count, package count, weight-unit consistency, etc.) — via
 * {@see \Medzuch\DhlExpress\Builder\CreateShipmentBuilder}.
 */
final readonly class CreateShipmentRequest
{
    /**
     * @param list<Account>             $accounts
     * @param list<ValueAddedService>   $valueAddedServices
     */
    public function __construct(
        public DateTimeImmutable $plannedShippingDateAndTime,
        public Pickup $pickup,
        public string $productCode,
        public array $accounts,
        public CustomerDetails $customerDetails,
        public Content $content,
        public ?string $localProductCode = null,
        public array $valueAddedServices = [],
        public ?OutputImageProperties $outputImageProperties = null,
        public ?bool $getRateEstimates = null,
    ) {
    }

    /**
     * Serialize the request to the DHL JSON wire shape.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'plannedShippingDateAndTime' => $this->plannedShippingDateAndTime->format('Y-m-d\TH:i:s\G\M\TP'),
            'pickup' => $this->pickup->toArray(),
            'productCode' => $this->productCode,
            'accounts' => array_map(
                static fn (Account $account): array => $account->toArray(),
                $this->accounts,
            ),
            'customerDetails' => $this->customerDetails->toArray(),
            'content' => $this->content->toArray(),
        ];

        if ($this->localProductCode !== null) {
            $payload['localProductCode'] = $this->localProductCode;
        }
        if ($this->valueAddedServices !== []) {
            $payload['valueAddedServices'] = array_map(
                static fn (ValueAddedService $service): array => $service->toArray(),
                $this->valueAddedServices,
            );
        }
        if ($this->outputImageProperties !== null) {
            $payload['outputImageProperties'] = $this->outputImageProperties->toArray();
        }
        if ($this->getRateEstimates !== null) {
            $payload['getRateEstimates'] = $this->getRateEstimates;
        }

        return $payload;
    }
}

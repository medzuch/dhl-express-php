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
 *
 * Note: the spec schema has `additionalProperties: false` at the root.
 * Fields like `dangerousGoods` that look top-level in older code
 * actually live INSIDE the matching `valueAddedServices[]` item — see
 * {@see ValueAddedService}.
 *
 * `customerReferences` reuses {@see PackageReference} since the
 * shipment-level and package-level reference items share the same
 * `{value, typeCode}` shape (spec schema
 * `supermodelIoLogisticsExpressReference`).
 */
final readonly class CreateShipmentRequest
{
    /**
     * @param list<Account>                       $accounts
     * @param list<ValueAddedService>             $valueAddedServices
     * @param list<PackageReference>              $customerReferences         max 999 per spec
     * @param list<Identifier>                    $identifiers                max 5 per spec
     * @param list<DocumentImage>                 $documentImages             max 999 per spec
     * @param list<ShipmentNotification>          $shipmentNotification       max 5 per spec
     * @param list<PrepaidCharge>                 $prepaidCharges             max 1 per spec
     * @param list<AdditionalInformationRequest>  $getAdditionalInformation   max 5 per spec
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
        public array $customerReferences = [],
        public array $identifiers = [],
        public array $documentImages = [],
        public ?OnDemandDelivery $onDemandDelivery = null,
        public ?bool $requestOndemandDeliveryURL = null,
        public array $shipmentNotification = [],
        public array $prepaidCharges = [],
        public ?bool $getTransliteratedResponse = null,
        public ?EstimatedDeliveryDateRequest $estimatedDeliveryDate = null,
        public array $getAdditionalInformation = [],
        public ?ParentShipment $parentShipment = null,
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
        if ($this->customerReferences !== []) {
            $payload['customerReferences'] = array_map(
                static fn (PackageReference $reference): array => $reference->toArray(),
                $this->customerReferences,
            );
        }
        if ($this->identifiers !== []) {
            $payload['identifiers'] = array_map(
                static fn (Identifier $identifier): array => $identifier->toArray(),
                $this->identifiers,
            );
        }
        if ($this->documentImages !== []) {
            $payload['documentImages'] = array_map(
                static fn (DocumentImage $image): array => $image->toArray(),
                $this->documentImages,
            );
        }
        if ($this->onDemandDelivery !== null) {
            $payload['onDemandDelivery'] = $this->onDemandDelivery->toArray();
        }
        if ($this->requestOndemandDeliveryURL !== null) {
            $payload['requestOndemandDeliveryURL'] = $this->requestOndemandDeliveryURL;
        }
        if ($this->shipmentNotification !== []) {
            $payload['shipmentNotification'] = array_map(
                static fn (ShipmentNotification $notification): array => $notification->toArray(),
                $this->shipmentNotification,
            );
        }
        if ($this->prepaidCharges !== []) {
            $payload['prepaidCharges'] = array_map(
                static fn (PrepaidCharge $charge): array => $charge->toArray(),
                $this->prepaidCharges,
            );
        }
        if ($this->getTransliteratedResponse !== null) {
            $payload['getTransliteratedResponse'] = $this->getTransliteratedResponse;
        }
        if ($this->estimatedDeliveryDate !== null) {
            $payload['estimatedDeliveryDate'] = $this->estimatedDeliveryDate->toArray();
        }
        if ($this->getAdditionalInformation !== []) {
            $payload['getAdditionalInformation'] = array_map(
                static fn (AdditionalInformationRequest $request): array => $request->toArray(),
                $this->getAdditionalInformation,
            );
        }
        if ($this->parentShipment !== null) {
            $payload['parentShipment'] = $this->parentShipment->toArray();
        }

        return $payload;
    }
}

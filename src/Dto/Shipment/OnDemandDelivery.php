<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\OnDemandDeliveryOption;
use Medzuch\DhlExpress\Enum\OnDemandWhereToLeave;

/**
 * Configuration for DHL Express On Demand Delivery (ODD) on a
 * create-shipment request.
 *
 * Mirrors the inline `onDemandDelivery` object in
 * `supermodelIoLogisticsExpressCreateShipmentRequest` (spec lines
 * 12450–12539). When set, the spec also requires `buyerDetails` on the
 * customerDetails block — that cross-field rule lives in the builder,
 * not this DTO.
 *
 * Only `deliveryOption` is required; the other fields are conditional
 * on the chosen option (e.g. `servicePointId` only matters when
 * `deliveryOption == servicepoint`).
 */
final readonly class OnDemandDelivery
{
    public function __construct(
        public OnDemandDeliveryOption $deliveryOption,
        public ?string $location = null,
        public ?string $specialInstructions = null,
        public ?string $gateCode = null,
        public ?OnDemandWhereToLeave $whereToLeave = null,
        public ?string $neighbourName = null,
        public ?string $neighbourHouseNumber = null,
        public ?string $authorizerName = null,
        public ?string $servicePointId = null,
        public ?string $requestedDeliveryDate = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = ['deliveryOption' => $this->deliveryOption->value];

        if ($this->location !== null) {
            $payload['location'] = $this->location;
        }
        if ($this->specialInstructions !== null) {
            $payload['specialInstructions'] = $this->specialInstructions;
        }
        if ($this->gateCode !== null) {
            $payload['gateCode'] = $this->gateCode;
        }
        if ($this->whereToLeave !== null) {
            $payload['whereToLeave'] = $this->whereToLeave->value;
        }
        if ($this->neighbourName !== null) {
            $payload['neighbourName'] = $this->neighbourName;
        }
        if ($this->neighbourHouseNumber !== null) {
            $payload['neighbourHouseNumber'] = $this->neighbourHouseNumber;
        }
        if ($this->authorizerName !== null) {
            $payload['authorizerName'] = $this->authorizerName;
        }
        if ($this->servicePointId !== null) {
            $payload['servicePointId'] = $this->servicePointId;
        }
        if ($this->requestedDeliveryDate !== null) {
            $payload['requestedDeliveryDate'] = $this->requestedDeliveryDate;
        }

        return $payload;
    }
}

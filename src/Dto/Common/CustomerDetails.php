<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Common;

/**
 * The shipper / receiver pair shared by `/rates` and `/landed-cost`
 * request bodies.
 *
 * Mirrors the inline `customerDetails` object in
 * `supermodelIoLogisticsExpressRateRequest` and
 * `supermodelIoLogisticsExpressLandedCostRequest`.
 */
final readonly class CustomerDetails
{
    public function __construct(
        public RateAddress $shipperDetails,
        public RateAddress $receiverDetails,
    ) {
    }

    /**
     * @return array{shipperDetails: array<string, string>, receiverDetails: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'shipperDetails' => $this->shipperDetails->toArray(),
            'receiverDetails' => $this->receiverDetails->toArray(),
        ];
    }
}

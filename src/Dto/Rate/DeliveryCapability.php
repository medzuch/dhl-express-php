<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

/**
 * Delivery-side capability summary for a quoted product.
 */
final readonly class DeliveryCapability
{
    public function __construct(
        public ?string $deliveryTypeCode,
        public ?string $estimatedDeliveryDateAndTime,
        public ?string $destinationServiceAreaCode,
        public ?string $destinationFacilityAreaCode,
        public ?float $deliveryAdditionalDays,
        public ?int $deliveryDayOfWeek,
        public ?int $totalTransitDays,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ServicePoint;

use Medzuch\DhlExpress\Enum\ServicePointType;

/**
 * One DHL Service Point returned by `GET /servicepoints`.
 *
 * Captures the fields needed to display, route to, and decide
 * whether the facility is currently open. The full OpenAPI schema
 * exposes additional fields (capabilities, partner, capacity,
 * shipment limitations, …) — those can be modelled when a concrete
 * caller surfaces a need.
 *
 * `servicePointType` is null when DHL returns a value outside the
 * documented enum; the raw string is preserved on
 * {@see $rawServicePointType}.
 */
final readonly class ServicePoint
{
    /**
     * @param list<OpeningTime> $openingHours
     */
    public function __construct(
        public string $facilityId,
        public string $serviceAreaCode,
        public string $servicePointName,
        public string $localName,
        public ?ServicePointType $servicePointType,
        public string $rawServicePointType,
        public ?ServicePointAddress $address,
        public ?GeoLocation $geoLocation,
        public string $distance,
        public string $shippingCutOffTime,
        public array $openingHours,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Tracking;

/**
 * Response from `GET /shipments/{id}/tracking`.
 *
 * Phase 1 surface: tracking number, status, description, and the
 * ordered list of {@see ShipmentEvent}. Shipper and receiver details,
 * piece events, and the rest of DHL's tracking schema land in Phase 3.
 */
final readonly class TrackingResponse
{
    /**
     * @param list<ShipmentEvent> $events
     */
    public function __construct(
        public string $shipmentTrackingNumber,
        public string $status,
        public string $description,
        public array $events,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Tracking;

/** Response from `GET /shipments/{id}/tracking`. */
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

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Tracking;

/**
 * A single event in a shipment's tracking history.
 *
 * Phase 1 keeps this DTO intentionally small (date / time / typeCode /
 * description). Phase 3 will expand it to cover the full event shape
 * from `dhl_openapi.yaml` (service areas, signatories, remarks, etc.).
 */
final readonly class ShipmentEvent
{
    public function __construct(
        public string $date,
        public string $time,
        public string $typeCode,
        public string $description,
    ) {
    }
}

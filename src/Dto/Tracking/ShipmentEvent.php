<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Tracking;

/** A single event in a shipment's tracking history. */
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

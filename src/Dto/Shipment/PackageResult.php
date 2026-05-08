<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * One package entry in the create-shipment response.
 *
 * Carries the per-piece tracking number DHL assigns plus any
 * piece-level documents (typically QR codes for the package).
 */
final readonly class PackageResult
{
    /**
     * @param list<ShipmentDocument> $documents
     */
    public function __construct(
        public string $trackingNumber,
        public ?int $referenceNumber = null,
        public ?string $trackingUrl = null,
        public ?float $volumetricWeight = null,
        public array $documents = [],
    ) {
    }
}

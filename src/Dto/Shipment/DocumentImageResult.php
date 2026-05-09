<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * One document entry in a `GET /shipments/{id}/get-image` response.
 */
final readonly class DocumentImageResult
{
    public function __construct(
        public string $shipmentTrackingNumber,
        public string $typeCode,
        public string $encodingFormat,
        public string $content,
        public ?string $function = null,
    ) {
    }
}

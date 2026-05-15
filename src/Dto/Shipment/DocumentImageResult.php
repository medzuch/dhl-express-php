<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\DocumentFunction;

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
        public ?DocumentFunction $function = null,
    ) {
    }
}

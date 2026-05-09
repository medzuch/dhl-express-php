<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Response from `GET /shipments/{id}/get-image`.
 */
final readonly class GetImageResponse
{
    /**
     * @param list<DocumentImageResult> $documents
     */
    public function __construct(
        public array $documents,
    ) {
    }
}

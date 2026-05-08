<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Epod;

/**
 * Response from `GET /shipments/{trackingNumber}/proof-of-delivery`.
 *
 * `documents` is the list of POD documents DHL returned — usually
 * one PDF, but the API allows for more depending on the requested
 * `content` variant.
 */
final readonly class EpodResponse
{
    /**
     * @param list<EpodDocument> $documents
     */
    public function __construct(
        public array $documents,
    ) {
    }
}

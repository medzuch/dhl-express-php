<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ServicePoint;

/**
 * Response from `GET /servicepoints`.
 *
 * `servicePoints` is the list of facilities matching the search;
 * `searchAddress` echoes the resolved address DHL used for the
 * lookup (empty when the request was placed by ID).
 */
final readonly class ServicePointFindResponse
{
    /**
     * @param list<ServicePoint> $servicePoints
     */
    public function __construct(
        public string $searchAddress,
        public array $servicePoints,
    ) {
    }
}

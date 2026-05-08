<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ServicePoint;

/**
 * Geo-coordinates of a Service Point.
 *
 * Coordinates are kept as nullable floats — DHL omits them when the
 * facility has not been geocoded yet.
 */
final readonly class GeoLocation
{
    public function __construct(
        public ?float $latitude,
        public ?float $longitude,
    ) {
    }
}

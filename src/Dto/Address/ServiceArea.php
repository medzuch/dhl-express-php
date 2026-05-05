<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Address;

/**
 * DHL service area attached to a validated address.
 *
 * The 3-letter `code` (PRG, LON, NYC, …) is DHL's internal
 * gateway identifier; `description` carries the human-readable
 * name, and `gmtOffset` is a `±HH:MM` string sourced straight
 * from the API response.
 */
final readonly class ServiceArea
{
    public function __construct(
        public string $code,
        public string $description,
        public string $gmtOffset,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ServicePoint;

/**
 * Subset of the OpenAPI `Address` schema used by the /servicepoints
 * endpoint.
 *
 * Only the fields actually populated by DHL on a typical sandbox
 * response are modelled — the rest stay accessible via the raw
 * response if a caller really needs them.
 */
final readonly class ServicePointAddress
{
    public function __construct(
        public string $addressLine1,
        public string $addressLine2,
        public string $addressLine3,
        public string $city,
        public string $zipCode,
        public string $state,
        public string $country,
        public string $countryDivisionCode,
    ) {
    }
}

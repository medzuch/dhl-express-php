<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Address;

/**
 * One address record returned from `GET /address-validate`.
 *
 * `countryCode` and `postalCode` are guaranteed by the API; city
 * and county may be empty strings if DHL didn't resolve them.
 * `serviceArea` is null when DHL has no service-area mapping.
 */
final readonly class ValidatedAddress
{
    public function __construct(
        public string $countryCode,
        public string $postalCode,
        public string $cityName,
        public string $countyName,
        public ?ServiceArea $serviceArea,
    ) {
    }
}

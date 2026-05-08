<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Common;

use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PostalCode;

/**
 * Address shape used by `/rates`, `/products`, and `/landed-cost`.
 *
 * Mirrors `supermodelIoLogisticsExpressAddressRatesRequest`. DHL
 * marks `postalCode`, `cityName`, and `countryCode` as required —
 * everything else is optional. `postalCode` may be empty for
 * countries that don't use postcodes; we keep it required at type
 * level to match the spec but allow empty values via `PostalCode`.
 */
final readonly class RateAddress
{
    public function __construct(
        public CountryCode $countryCode,
        public PostalCode $postalCode,
        public string $cityName,
        public ?string $provinceCode = null,
        public ?string $addressLine1 = null,
        public ?string $addressLine2 = null,
        public ?string $addressLine3 = null,
        public ?string $countyName = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = [
            'postalCode' => $this->postalCode->value,
            'cityName' => $this->cityName,
            'countryCode' => $this->countryCode->value,
        ];

        if ($this->provinceCode !== null) {
            $payload['provinceCode'] = $this->provinceCode;
        }
        if ($this->addressLine1 !== null) {
            $payload['addressLine1'] = $this->addressLine1;
        }
        if ($this->addressLine2 !== null) {
            $payload['addressLine2'] = $this->addressLine2;
        }
        if ($this->addressLine3 !== null) {
            $payload['addressLine3'] = $this->addressLine3;
        }
        if ($this->countyName !== null) {
            $payload['countyName'] = $this->countyName;
        }

        return $payload;
    }
}

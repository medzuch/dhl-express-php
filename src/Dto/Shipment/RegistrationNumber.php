<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\RegistrationNumberTypeCode;
use Medzuch\DhlExpress\ValueObject\CountryCode;

/**
 * One entry in a party's `registrationNumbers` array.
 *
 * Mirrors `supermodelIoLogisticsExpressRegistrationNumbers`. The full
 * country/role applicability matrix for each `typeCode` (e.g. CNP is
 * BR-only, SDT is shipper-only) lives in the workbook and is enforced
 * by request builders rather than this DTO.
 */
final readonly class RegistrationNumber
{
    public function __construct(
        public RegistrationNumberTypeCode $typeCode,
        public string $number,
        public CountryCode $issuerCountryCode,
    ) {
    }

    /**
     * @return array{typeCode: string, number: string, issuerCountryCode: string}
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode->value,
            'number' => $this->number,
            'issuerCountryCode' => $this->issuerCountryCode->value,
        ];
    }
}

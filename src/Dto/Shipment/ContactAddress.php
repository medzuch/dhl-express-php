<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\EmailAddress;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;

/**
 * Address + contact composite for a shipment customer party (shipper
 * or receiver).
 *
 * Mirrors the inline `shipperDetails` / `receiverDetails` shape under
 * `customerDetails`. Each is `{postalAddress: {...}, contactInformation:
 * {...}}`. We model both halves on a single class because they always
 * appear together — splitting into two DTOs would add files without
 * adding clarity at this scope.
 *
 * Distinct from {@see \Medzuch\DhlExpress\Dto\Common\RateAddress},
 * which has no contact fields — `/rates` only needs geography.
 *
 */
final readonly class ContactAddress
{
    public function __construct(
        // Postal address — required fields:
        public CountryCode $countryCode,
        public PostalCode $postalCode,
        public string $cityName,
        public string $addressLine1,
        // Contact information — required fields:
        public PhoneNumber $phone,
        public string $companyName,
        public string $fullName,
        // Postal address — optional fields:
        public ?string $addressLine2 = null,
        public ?string $addressLine3 = null,
        public ?string $provinceCode = null,
        public ?string $provinceName = null,
        public ?string $countyName = null,
        public ?string $countryName = null,
        // Contact information — optional fields:
        public ?EmailAddress $email = null,
        public ?PhoneNumber $mobilePhone = null,
    ) {
    }

    /**
     * @return array{postalAddress: array<string, string>, contactInformation: array<string, string>}
     */
    public function toArray(): array
    {
        $postalAddress = [
            'postalCode' => $this->postalCode->value,
            'cityName' => $this->cityName,
            'countryCode' => $this->countryCode->value,
            'addressLine1' => $this->addressLine1,
        ];

        if ($this->addressLine2 !== null) {
            $postalAddress['addressLine2'] = $this->addressLine2;
        }
        if ($this->addressLine3 !== null) {
            $postalAddress['addressLine3'] = $this->addressLine3;
        }
        if ($this->provinceCode !== null) {
            $postalAddress['provinceCode'] = $this->provinceCode;
        }
        if ($this->provinceName !== null) {
            $postalAddress['provinceName'] = $this->provinceName;
        }
        if ($this->countyName !== null) {
            $postalAddress['countyName'] = $this->countyName;
        }
        if ($this->countryName !== null) {
            $postalAddress['countryName'] = $this->countryName;
        }

        $contactInformation = [
            'phone' => $this->phone->value,
            'companyName' => $this->companyName,
            'fullName' => $this->fullName,
        ];

        if ($this->email !== null) {
            $contactInformation['email'] = $this->email->value;
        }
        if ($this->mobilePhone !== null) {
            $contactInformation['mobilePhone'] = $this->mobilePhone->value;
        }

        return [
            'postalAddress' => $postalAddress,
            'contactInformation' => $contactInformation,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\BankDetails;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\RegistrationNumber;
use Medzuch\DhlExpress\Dto\Shipment\ShipmentParty;
use Medzuch\DhlExpress\Enum\InvoicePartyTypeCode;
use Medzuch\DhlExpress\Enum\RegistrationNumberTypeCode;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use PHPUnit\Framework\TestCase;

final class ShipmentPartyTest extends TestCase
{
    public function testToArrayWithContactOnlyMatchesContactAddressShape(): void
    {
        $party = new ShipmentParty(contact: $this->makeContact());

        $payload = $party->toArray();

        self::assertArrayHasKey('postalAddress', $payload);
        self::assertArrayHasKey('contactInformation', $payload);
        self::assertArrayNotHasKey('registrationNumbers', $payload);
        self::assertArrayNotHasKey('bankDetails', $payload);
        self::assertArrayNotHasKey('typeCode', $payload);
    }

    public function testToArrayWithAllOptionalFields(): void
    {
        $party = new ShipmentParty(
            contact: $this->makeContact(),
            registrationNumbers: [
                new RegistrationNumber(
                    typeCode: RegistrationNumberTypeCode::VAT,
                    number: 'CZ12345678',
                    issuerCountryCode: new CountryCode('CZ'),
                ),
            ],
            bankDetails: new BankDetails(name: 'Test Bank'),
            typeCode: InvoicePartyTypeCode::Business,
        );

        $payload = $party->toArray();

        self::assertCount(1, $payload['registrationNumbers']);
        self::assertSame('VAT', $payload['registrationNumbers'][0]['typeCode']);
        // bankDetails is a single-element array per spec maxItems:1
        self::assertIsArray($payload['bankDetails']);
        self::assertCount(1, $payload['bankDetails']);
        self::assertSame('Test Bank', $payload['bankDetails'][0]['name']);
        self::assertSame('business', $payload['typeCode']);
    }

    private function makeContact(): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
            addressLine1: 'Vaclavske namesti 1',
            phone: new PhoneNumber('+420 222 333 444'),
            companyName: 'Buyer s.r.o.',
            fullName: 'Buyer Name',
        );
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\CustomerDetails;
use Medzuch\DhlExpress\Dto\Shipment\ShipmentParty;
use Medzuch\DhlExpress\Enum\InvoicePartyTypeCode;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use PHPUnit\Framework\TestCase;

final class CustomerDetailsTest extends TestCase
{
    public function testToArrayWithRequiredPartiesOnly(): void
    {
        $details = new CustomerDetails(
            shipperDetails: $this->makeContact('Prague'),
            receiverDetails: $this->makeContact('Brno'),
        );

        $payload = $details->toArray();

        self::assertArrayHasKey('shipperDetails', $payload);
        self::assertArrayHasKey('receiverDetails', $payload);
        self::assertArrayNotHasKey('buyerDetails', $payload);
        self::assertArrayNotHasKey('brokerDetails', $payload);
    }

    public function testAllOptionalPartiesAppearInPayloadWhenSet(): void
    {
        $party = new ShipmentParty(
            contact: $this->makeContact('Buyer City'),
            typeCode: InvoicePartyTypeCode::Business,
        );

        $details = new CustomerDetails(
            shipperDetails: $this->makeContact('Prague'),
            receiverDetails: $this->makeContact('Brno'),
            buyerDetails: $party,
            importerDetails: $party,
            exporterDetails: $party,
            sellerDetails: $party,
            payerDetails: $party,
            manufacturerDetails: $party,
            ultimateConsigneeDetails: $party,
            brokerDetails: $party,
        );

        $payload = $details->toArray();

        self::assertArrayHasKey('buyerDetails', $payload);
        self::assertArrayHasKey('importerDetails', $payload);
        self::assertArrayHasKey('exporterDetails', $payload);
        self::assertArrayHasKey('sellerDetails', $payload);
        self::assertArrayHasKey('payerDetails', $payload);
        self::assertArrayHasKey('manufacturerDetails', $payload);
        self::assertArrayHasKey('ultimateConsigneeDetails', $payload);
        self::assertArrayHasKey('brokerDetails', $payload);
        self::assertSame('business', $payload['buyerDetails']['typeCode']);
    }

    private function makeContact(string $city): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: $city,
            addressLine1: 'Street 1',
            phone: new PhoneNumber('+420 222 333 444'),
            companyName: 'Co.',
            fullName: 'Name',
        );
    }
}

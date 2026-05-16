<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Pickup\PickupSpecialInstruction;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\Pickup;
use Medzuch\DhlExpress\Dto\Shipment\ShipmentParty;
use Medzuch\DhlExpress\Enum\InvoicePartyTypeCode;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use PHPUnit\Framework\TestCase;

final class PickupTest extends TestCase
{
    public function testIsRequestedOnlyMatchesPreviousBehavior(): void
    {
        $pickup = new Pickup(false);

        self::assertSame(['isRequested' => false], $pickup->toArray());
    }

    public function testCloseTimeAndLocationSerialize(): void
    {
        $pickup = new Pickup(
            isRequested: true,
            closeTime: '18:00',
            location: 'reception',
        );

        $payload = $pickup->toArray();

        self::assertTrue($payload['isRequested']);
        self::assertSame('18:00', $payload['closeTime']);
        self::assertSame('reception', $payload['location']);
    }

    public function testSpecialInstructionsSerializeAsArray(): void
    {
        $pickup = new Pickup(
            isRequested: true,
            specialInstructions: [
                new PickupSpecialInstruction(value: 'please ring door bell'),
                new PickupSpecialInstruction(value: 'leave at reception', typeCode: 'TBD'),
            ],
        );

        $payload = $pickup->toArray();

        self::assertCount(2, $payload['specialInstructions']);
        self::assertSame('please ring door bell', $payload['specialInstructions'][0]['value']);
        self::assertSame('TBD', $payload['specialInstructions'][1]['typeCode']);
    }

    public function testPickupDetailsAndRequestorDetailsReuseShipmentPartyShape(): void
    {
        $contact = $this->makeContact();

        $pickup = new Pickup(
            isRequested: true,
            pickupDetails: new ShipmentParty(
                contact: $contact,
                typeCode: InvoicePartyTypeCode::Business,
            ),
            pickupRequestorDetails: new ShipmentParty(contact: $contact),
        );

        $payload = $pickup->toArray();

        self::assertArrayHasKey('pickupDetails', $payload);
        self::assertArrayHasKey('postalAddress', $payload['pickupDetails']);
        self::assertSame('business', $payload['pickupDetails']['typeCode']);

        self::assertArrayHasKey('pickupRequestorDetails', $payload);
        self::assertArrayHasKey('contactInformation', $payload['pickupRequestorDetails']);
    }

    public function testOptionalFieldsOmittedWhenNotSet(): void
    {
        $payload = (new Pickup(true))->toArray();

        self::assertArrayNotHasKey('closeTime', $payload);
        self::assertArrayNotHasKey('location', $payload);
        self::assertArrayNotHasKey('specialInstructions', $payload);
        self::assertArrayNotHasKey('pickupDetails', $payload);
        self::assertArrayNotHasKey('pickupRequestorDetails', $payload);
    }

    private function makeContact(): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
            addressLine1: 'Vaclavske namesti 1',
            phone: new PhoneNumber('+420 222 333 444'),
            companyName: 'Co.',
            fullName: 'Name',
        );
    }
}

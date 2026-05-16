<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\AdditionalInformationRequest;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\Content;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentRequest;
use Medzuch\DhlExpress\Dto\Shipment\CustomerDetails;
use Medzuch\DhlExpress\Dto\Shipment\DocumentImage;
use Medzuch\DhlExpress\Dto\Shipment\EstimatedDeliveryDateRequest;
use Medzuch\DhlExpress\Dto\Shipment\Identifier;
use Medzuch\DhlExpress\Dto\Shipment\OnDemandDelivery;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Dto\Shipment\PackageReference;
use Medzuch\DhlExpress\Dto\Shipment\ParentShipment;
use Medzuch\DhlExpress\Dto\Shipment\Pickup;
use Medzuch\DhlExpress\Dto\Shipment\PrepaidCharge;
use Medzuch\DhlExpress\Dto\Shipment\ShipmentNotification;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\AdditionalInformationType;
use Medzuch\DhlExpress\Enum\DocumentImageFormat;
use Medzuch\DhlExpress\Enum\DocumentImageTypeCode;
use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateType;
use Medzuch\DhlExpress\Enum\IdentifierTypeCode;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\OnDemandDeliveryOption;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class CreateShipmentRequestOptionalFieldsTest extends TestCase
{
    public function testAllOptionalRootFieldsSerialize(): void
    {
        $request = new CreateShipmentRequest(
            plannedShippingDateAndTime: new DateTimeImmutable('2026-06-01T13:00:00+00:00'),
            pickup: new Pickup(false),
            productCode: 'N',
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            customerDetails: new CustomerDetails(
                shipperDetails: $this->makeContact('Prague'),
                receiverDetails: $this->makeContact('Brno'),
            ),
            content: new Content(
                packages: [new Package(weight: new Weight(1.0, WeightUnit::KG))],
                isCustomsDeclarable: false,
                description: 'Books',
                incoterm: Incoterm::DAP,
                unitOfMeasurement: UnitSystem::Metric,
            ),
            customerReferences: [new PackageReference('REF-1')],
            identifiers: [
                new Identifier(typeCode: IdentifierTypeCode::ShipmentId, value: 'WB123'),
            ],
            documentImages: [
                new DocumentImage('base64data', DocumentImageTypeCode::INV, DocumentImageFormat::PDF),
            ],
            onDemandDelivery: new OnDemandDelivery(
                deliveryOption: OnDemandDeliveryOption::Servicepoint,
                servicePointId: 'SPL123',
            ),
            requestOndemandDeliveryURL: true,
            shipmentNotification: [
                new ShipmentNotification(receiverId: 'a@b.c'),
            ],
            prepaidCharges: [
                new PrepaidCharge(currency: new CurrencyCode('EUR'), value: 12.34),
            ],
            getTransliteratedResponse: true,
            estimatedDeliveryDate: new EstimatedDeliveryDateRequest(
                isRequested: true,
                typeCode: EstimatedDeliveryDateType::QDDC,
            ),
            getAdditionalInformation: [
                new AdditionalInformationRequest(
                    typeCode: AdditionalInformationType::PickupDetails,
                    isRequested: true,
                ),
            ],
            parentShipment: new ParentShipment(productCode: 'P', packagesCount: 3),
        );

        $payload = $request->toArray();

        self::assertSame([['value' => 'REF-1']], $payload['customerReferences']);
        self::assertSame('WB123', $payload['identifiers'][0]['value']);
        self::assertSame('INV', $payload['documentImages'][0]['typeCode']);
        self::assertSame('servicepoint', $payload['onDemandDelivery']['deliveryOption']);
        self::assertSame('SPL123', $payload['onDemandDelivery']['servicePointId']);
        self::assertTrue($payload['requestOndemandDeliveryURL']);
        self::assertSame('email', $payload['shipmentNotification'][0]['typeCode']);
        self::assertSame('a@b.c', $payload['shipmentNotification'][0]['receiverId']);
        self::assertSame('freight', $payload['prepaidCharges'][0]['typeCode']);
        self::assertSame(12.34, $payload['prepaidCharges'][0]['value']);
        self::assertSame('cash', $payload['prepaidCharges'][0]['method']);
        self::assertTrue($payload['getTransliteratedResponse']);
        self::assertTrue($payload['estimatedDeliveryDate']['isRequested']);
        self::assertSame('QDDC', $payload['estimatedDeliveryDate']['typeCode']);
        self::assertSame('pickupDetails', $payload['getAdditionalInformation'][0]['typeCode']);
        self::assertSame('P', $payload['parentShipment']['productCode']);
        self::assertSame(3, $payload['parentShipment']['packagesCount']);
    }

    public function testOptionalRootFieldsAbsentWhenNotSet(): void
    {
        $request = new CreateShipmentRequest(
            plannedShippingDateAndTime: new DateTimeImmutable('2026-06-01T13:00:00+00:00'),
            pickup: new Pickup(false),
            productCode: 'N',
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            customerDetails: new CustomerDetails(
                shipperDetails: $this->makeContact('Prague'),
                receiverDetails: $this->makeContact('Brno'),
            ),
            content: new Content(
                packages: [new Package(weight: new Weight(1.0, WeightUnit::KG))],
                isCustomsDeclarable: false,
                description: 'Books',
                incoterm: Incoterm::DAP,
                unitOfMeasurement: UnitSystem::Metric,
            ),
        );

        $payload = $request->toArray();

        self::assertArrayNotHasKey('customerReferences', $payload);
        self::assertArrayNotHasKey('identifiers', $payload);
        self::assertArrayNotHasKey('documentImages', $payload);
        self::assertArrayNotHasKey('onDemandDelivery', $payload);
        self::assertArrayNotHasKey('requestOndemandDeliveryURL', $payload);
        self::assertArrayNotHasKey('shipmentNotification', $payload);
        self::assertArrayNotHasKey('prepaidCharges', $payload);
        self::assertArrayNotHasKey('getTransliteratedResponse', $payload);
        self::assertArrayNotHasKey('estimatedDeliveryDate', $payload);
        self::assertArrayNotHasKey('getAdditionalInformation', $payload);
        self::assertArrayNotHasKey('parentShipment', $payload);
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

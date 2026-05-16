<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use DateTimeImmutable;
use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\ShipmentApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentResponse;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class ShipmentApiTest extends TestCase
{
    public function testCreateSendsExpectedJsonBodyToShipmentsEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(201)->withBody($factory->createStream($this->loadFixture('create-shipment-success.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $request = $this->minimalDomesticBuilder()->build();
        $api->create($request);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('application/json', $sent->getHeaderLine('Content-Type'));
        self::assertStringEndsWith('/shipments', (string) $sent->getUri());

        $body = (string) $sent->getBody();
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($decoded);
        self::assertSame('N', $decoded['productCode']);
        self::assertFalse($decoded['pickup']['isRequested']);
        self::assertFalse($decoded['content']['isCustomsDeclarable']);
        self::assertCount(1, $decoded['accounts']);
        self::assertSame('shipper', $decoded['accounts'][0]['typeCode']);
        self::assertSame('Prague', $decoded['customerDetails']['shipperDetails']['postalAddress']['cityName']);
        self::assertSame('Brno', $decoded['customerDetails']['receiverDetails']['postalAddress']['cityName']);
        self::assertSame('Alfa Trading s.r.o.', $decoded['customerDetails']['shipperDetails']['contactInformation']['companyName']);
    }

    public function testCreateAppendsValidateDataOnlyQueryWhenRequested(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream($this->loadFixture('create-shipment-success.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $api->create($this->minimalDomesticBuilder()->build(), validateDataOnly: true);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertStringContainsString('/shipments?validateDataOnly=true', (string) $sent->getUri());
    }

    public function testCreateHydratesResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(201)->withBody($factory->createStream($this->loadFixture('create-shipment-success.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $response = $api->create($this->minimalDomesticBuilder()->build());

        self::assertInstanceOf(CreateShipmentResponse::class, $response);
        self::assertSame('1234567890', $response->shipmentTrackingNumber);
        self::assertSame('PRG200227000256', $response->dispatchConfirmationNumber);
        self::assertCount(1, $response->packages);
        self::assertSame('JD914600003889482921', $response->packages[0]->trackingNumber);
        self::assertSame(1.5, $response->packages[0]->volumetricWeight);
        self::assertCount(1, $response->packages[0]->documents);
        self::assertSame('qr-code', $response->packages[0]->documents[0]->typeCode);
        self::assertCount(2, $response->documents);
        self::assertSame('label', $response->documents[0]->typeCode);
        self::assertSame('PDF', $response->documents[0]->imageFormat);
    }

    private function minimalDomesticBuilder(): CreateShipmentBuilder
    {
        return (new CreateShipmentBuilder())
            ->withShipper(new ContactAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('14800'),
                cityName: 'Prague',
                addressLine1: 'Vaclavske namesti 1',
                phone: new PhoneNumber('+420 222 333 444'),
                companyName: 'Alfa Trading s.r.o.',
                fullName: 'Jan Nowak',
            ))
            ->withReceiver(new ContactAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('60200'),
                cityName: 'Brno',
                addressLine1: 'Namesti Svobody 10',
                phone: new PhoneNumber('+420 555 666 777'),
                companyName: 'Receiver s.r.o.',
                fullName: 'Receiver Name',
            ))
            ->withPlannedShippingDate(new DateTimeImmutable('2026-06-01T13:00:00+00:00'))
            ->withProductCode('N')
            ->withPickupRequested(false)
            ->withIsCustomsDeclarable(false)
            ->withContentDescription('Books')
            ->withUnitSystem(UnitSystem::Metric)
            ->withIncoterm(Incoterm::DAP)
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withPackage(new Package(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
            ));
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): ShipmentApi
    {
        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
        );
        $generator = new class () implements MessageReferenceGenerator {
            public function generate(): MessageReference
            {
                return new MessageReference('11111111-2222-4333-8444-555555555555');
            }
        };
        $builder = new RequestBuilder($factory, $factory, $generator, $config);
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()));

        return new ShipmentApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/shipments/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

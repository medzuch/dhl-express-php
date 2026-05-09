<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use DateTimeImmutable;
use DateTimeZone;
use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\PickupApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupRequest;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupResponse;
use Medzuch\DhlExpress\Dto\Pickup\PickupCustomerDetails;
use Medzuch\DhlExpress\Dto\Pickup\PickupPackage;
use Medzuch\DhlExpress\Dto\Pickup\PickupShipmentDetails;
use Medzuch\DhlExpress\Dto\Pickup\UpdatePickupRequest;
use Medzuch\DhlExpress\Dto\Pickup\UpdatePickupResponse;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class PickupApiTest extends TestCase
{
    public function testCreateSendsPostToPickupsEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(201)->withBody(
                $factory->createStream($this->loadFixture('create-pickup-success.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->create($this->minimalCreateRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertStringEndsWith('/pickups', (string) $sent->getUri());
        self::assertSame('application/json', $sent->getHeaderLine('Content-Type'));
    }

    public function testCreateSendsExpectedJsonBody(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(201)->withBody(
                $factory->createStream($this->loadFixture('create-pickup-success.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->create($this->minimalCreateRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);

        $body = json_decode((string) $sent->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);

        // top-level fields
        self::assertArrayHasKey('plannedPickupDateAndTime', $body);
        self::assertStringContainsString('GMT', (string) $body['plannedPickupDateAndTime']);
        self::assertCount(1, $body['accounts']);
        self::assertSame('shipper', $body['accounts'][0]['typeCode']);

        // customerDetails
        self::assertArrayHasKey('customerDetails', $body);
        self::assertSame('Prague', $body['customerDetails']['shipperDetails']['postalAddress']['cityName']);

        // shipmentDetails
        self::assertCount(1, $body['shipmentDetails']);
        self::assertSame('N', $body['shipmentDetails'][0]['productCode']);
        self::assertFalse($body['shipmentDetails'][0]['isCustomsDeclarable']);
        self::assertSame('metric', $body['shipmentDetails'][0]['unitOfMeasurement']);
        self::assertCount(1, $body['shipmentDetails'][0]['packages']);
        self::assertSame(1.5, $body['shipmentDetails'][0]['packages'][0]['weight']);
    }

    public function testCreateHydratesResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(201)->withBody(
                $factory->createStream($this->loadFixture('create-pickup-success.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $response = $api->create($this->minimalCreateRequest());

        self::assertInstanceOf(CreatePickupResponse::class, $response);
        self::assertSame(['PRG211126000382'], $response->dispatchConfirmationNumbers);
        self::assertSame('13:00', $response->readyByTime);
        self::assertSame('2026-06-02', $response->nextPickupDate);
        self::assertSame([], $response->warnings);
    }

    public function testUpdateSendsPatchToPickupsEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('update-pickup-success.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->update($this->minimalUpdateRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('PATCH', $sent->getMethod());
        self::assertStringContainsString('/pickups/PRG211126000382', (string) $sent->getUri());
    }

    public function testUpdateSendsExpectedJsonBody(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('update-pickup-success.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->update($this->minimalUpdateRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);

        $body = json_decode((string) $sent->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertSame('PRG211126000382', $body['dispatchConfirmationNumber']);
        self::assertSame('123456789', $body['originalShipperAccountNumber']);
        self::assertArrayHasKey('plannedPickupDateAndTime', $body);
    }

    public function testUpdateHydratesResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('update-pickup-success.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $response = $api->update($this->minimalUpdateRequest());

        self::assertInstanceOf(UpdatePickupResponse::class, $response);
        self::assertSame('PRG211126000382', $response->dispatchConfirmationNumber);
        self::assertSame('15:00', $response->readyByTime);
        self::assertSame('2026-06-03', $response->nextPickupDate);
        self::assertSame(['Pickup has been updated'], $response->warnings);
    }

    public function testCancelSendsDeleteWithQueryParams(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200));

        $api = $this->makeApi($mockClient, $factory);
        $api->cancel('PRG211126000382', 'Fred Brent', 'No longer needed');

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('DELETE', $sent->getMethod());

        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/pickups/PRG211126000382', $uri);
        self::assertStringContainsString('requestorName=', $uri);
        self::assertStringContainsString('Fred', urldecode($uri));
        self::assertStringContainsString('Brent', urldecode($uri));
        self::assertStringContainsString('reason=', $uri);
        self::assertStringContainsString('No longer needed', urldecode($uri));
    }

    private function minimalCreateRequest(): CreatePickupRequest
    {
        $shipper = new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
            addressLine1: 'Vaclavske namesti 1',
            phone: new PhoneNumber('+420 222 333 444'),
            companyName: 'Alfa Trading s.r.o.',
            fullName: 'Jan Nowak',
        );

        return new CreatePickupRequest(
            plannedPickupDateAndTime: new DateTimeImmutable('2026-06-02T13:00:00', new DateTimeZone('+01:00')),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            customerDetails: new PickupCustomerDetails(shipperDetails: $shipper),
            shipmentDetails: [
                new PickupShipmentDetails(
                    productCode: 'N',
                    isCustomsDeclarable: false,
                    unitOfMeasurement: UnitSystem::Metric,
                    packages: [new PickupPackage(weight: new Weight(1.5, WeightUnit::KG))],
                ),
            ],
        );
    }

    private function minimalUpdateRequest(): UpdatePickupRequest
    {
        $shipper = new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
            addressLine1: 'Vaclavske namesti 1',
            phone: new PhoneNumber('+420 222 333 444'),
            companyName: 'Alfa Trading s.r.o.',
            fullName: 'Jan Nowak',
        );

        return new UpdatePickupRequest(
            dispatchConfirmationNumber: 'PRG211126000382',
            originalShipperAccountNumber: '123456789',
            plannedPickupDateAndTime: new DateTimeImmutable('2026-06-03T15:00:00', new DateTimeZone('+01:00')),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            customerDetails: new PickupCustomerDetails(shipperDetails: $shipper),
        );
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): PickupApi
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

        return new PickupApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/pickups/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

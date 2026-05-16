<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use DateTimeImmutable;
use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\EarlyShipmentScreeningApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\EarlyShipmentScreening\EarlyShipmentScreeningCustomerDetails;
use Medzuch\DhlExpress\Dto\EarlyShipmentScreening\EarlyShipmentScreeningCustomerReference;
use Medzuch\DhlExpress\Dto\EarlyShipmentScreening\EarlyShipmentScreeningIdentifier;
use Medzuch\DhlExpress\Dto\EarlyShipmentScreening\EarlyShipmentScreeningRequest;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
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
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class EarlyShipmentScreeningApiTest extends TestCase
{
    public function testSendsPostRequestToCorrectEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream('{"status":"OK"}'),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $api->screen($this->makeRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertStringContainsString('/early-shipment-screening', (string) $sent->getUri());
    }

    public function testSendsExpectedJsonBody(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream('{"status":"OK"}'),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $api->screen($this->makeRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $body = json_decode((string) $sent->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertSame('2026-08-04T14:00:00', $body['plannedShippingDateAndTime']);
        self::assertSame('B', $body['productCode']);
        self::assertArrayHasKey('shipperDetails', $body['customerDetails']);
        self::assertArrayHasKey('receiverDetails', $body['customerDetails']);
        self::assertCount(1, $body['accounts']);
        self::assertSame('shipper', $body['accounts'][0]['typeCode']);
        self::assertSame('CU', $body['customerReferences'][0]['typeCode']);
        self::assertSame('shipmentId', $body['identifiers'][0]['typeCode']);
        self::assertSame('1234567890', $body['identifiers'][0]['value']);
    }

    public function testHydratesResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream(json_encode([
                    'status' => 'GREEN',
                    'warnings' => ['some values provided may be invalid'],
                ], JSON_THROW_ON_ERROR)),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $response = $api->screen($this->makeRequest());

        self::assertSame('GREEN', $response->status);
        self::assertSame(['some values provided may be invalid'], $response->warnings);
    }

    public function testHydratesResponseWithoutWarnings(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream('{"status":"RED"}'),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $response = $api->screen($this->makeRequest());

        self::assertSame('RED', $response->status);
        self::assertSame([], $response->warnings);
    }

    private function makeRequest(): EarlyShipmentScreeningRequest
    {
        return new EarlyShipmentScreeningRequest(
            plannedShippingDateAndTime: new DateTimeImmutable('2026-08-04T14:00:00'),
            productCode: 'B',
            customerDetails: new EarlyShipmentScreeningCustomerDetails(
                shipperDetails: $this->makeAddress('CZ'),
                receiverDetails: $this->makeAddress('DE'),
            ),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            customerReferences: [new EarlyShipmentScreeningCustomerReference(value: 'REF-123')],
            identifiers: [new EarlyShipmentScreeningIdentifier(new TrackingNumber('1234567890'))],
        );
    }

    private function makeAddress(string $countryCode): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode($countryCode),
            postalCode: new PostalCode('11000'),
            cityName: 'Prague',
            addressLine1: 'Václavské náměstí 1',
            phone: new PhoneNumber('+420123456789'),
            companyName: 'Acme s.r.o.',
            fullName: 'Jan Novák',
        );
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): EarlyShipmentScreeningApi
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

        return new EarlyShipmentScreeningApi($builder, $transport);
    }
}

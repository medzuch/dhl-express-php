<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\ServicePointApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\ServicePoint\ServicePointFindResponse;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\DayOfWeek;
use Medzuch\DhlExpress\Enum\ServicePointType;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class ServicePointApiTest extends TestCase
{
    public function testHydratesServicePointsFromResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('prague.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $result = $api->find(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('11000'),
        );

        self::assertInstanceOf(ServicePointFindResponse::class, $result);
        self::assertCount(2, $result->servicePoints);

        $first = $result->servicePoints[0];
        self::assertSame('PRG001', $first->facilityId);
        self::assertSame('PRG', $first->serviceAreaCode);
        self::assertSame(ServicePointType::Partner, $first->servicePointType);
        self::assertSame('PARTNER', $first->rawServicePointType);
        self::assertNotNull($first->address);
        self::assertSame('Wenceslas Square 12', $first->address->addressLine1);
        self::assertSame('11000', $first->address->zipCode);
        self::assertNotNull($first->geoLocation);
        self::assertSame(50.0815, $first->geoLocation->latitude);
        self::assertSame(14.4287, $first->geoLocation->longitude);
        self::assertSame('0.4', $first->distance);
        self::assertSame('16:00', $first->shippingCutOffTime);

        self::assertCount(2, $first->openingHours);
        self::assertSame(DayOfWeek::Monday, $first->openingHours[0]->dayOfWeek);
        self::assertSame('09:00', $first->openingHours[0]->openingTime);
        self::assertSame('18:00', $first->openingHours[0]->closingTime);
        self::assertSame(DayOfWeek::Friday, $first->openingHours[1]->dayOfWeek);
    }

    public function testHandlesPartialServicePoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('prague.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $result = $api->find(countryCode: new CountryCode('CZ'));

        $second = $result->servicePoints[1];
        self::assertSame('PRG002', $second->facilityId);
        self::assertSame(ServicePointType::Station, $second->servicePointType);
        self::assertSame('', $second->localName);
        self::assertSame('', $second->shippingCutOffTime);
        self::assertSame([], $second->openingHours);
    }

    public function testFallsBackForUnknownEnumValues(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream(
                    json_encode([
                        'servicePoints' => [[
                            'facilityId' => 'XXX001',
                            'servicePointType' => 'UNKNOWN_TYPE',
                            'openingHours' => [
                                'openingHours' => [[
                                    'dayOfWeek' => 'WEEKEND',
                                    'openingTime' => '10:00',
                                    'closingTime' => '14:00',
                                ]],
                            ],
                        ]],
                    ], JSON_THROW_ON_ERROR),
                ),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $result = $api->find(countryCode: new CountryCode('CZ'));

        $sp = $result->servicePoints[0];
        self::assertNull($sp->servicePointType);
        self::assertSame('UNKNOWN_TYPE', $sp->rawServicePointType);
        self::assertNull($sp->openingHours[0]->dayOfWeek);
        self::assertSame('WEEKEND', $sp->openingHours[0]->rawDayOfWeek);
    }

    public function testEmitsExpectedQueryParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('prague.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->find(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('11000'),
            cityName: 'Prague',
            resultLimit: 5,
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/servicepoints?', $uri);
        self::assertStringContainsString('countryCode=CZ', $uri);
        self::assertStringContainsString('postalCode=11000', $uri);
        self::assertStringContainsString('city=Prague', $uri);
        self::assertStringContainsString('servicePointResults=5', $uri);
    }

    public function testOmitsAllParametersWhenNotProvided(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream('{"servicePoints":[]}'),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->find();

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringEndsWith('/servicepoints', $uri);
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): ServicePointApi
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

        return new ServicePointApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/servicepoints/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

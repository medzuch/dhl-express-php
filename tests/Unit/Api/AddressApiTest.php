<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\AddressApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Address\AddressValidateResponse;
use Medzuch\DhlExpress\Enum\AddressValidationType;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
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

final class AddressApiTest extends TestCase
{
    public function testHydratesValidatedAddressWithServiceArea(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('prague-pickup.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $result = $api->validate(
            AddressValidationType::Pickup,
            new CountryCode('CZ'),
            new PostalCode('14800'),
            'Prague',
        );

        self::assertInstanceOf(AddressValidateResponse::class, $result);
        self::assertSame([], $result->warnings);
        self::assertCount(1, $result->addresses);
        $address = $result->addresses[0];
        self::assertSame('CZ', $address->countryCode);
        self::assertSame('14800', $address->postalCode);
        self::assertSame('PRAGUE', $address->cityName);
        self::assertSame('PRAHA 4', $address->countyName);
        self::assertNotNull($address->serviceArea);
        self::assertSame('PRG', $address->serviceArea->code);
        self::assertSame('+01:00', $address->serviceArea->gmtOffset);
    }

    public function testEmitsRequiredAndOptionalQueryParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('prague-pickup.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->validate(
            AddressValidationType::Delivery,
            new CountryCode('CZ'),
            new PostalCode('14800'),
            'Prague',
            'Praha 4',
            true,
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/address-validate?', $uri);
        self::assertStringContainsString('type=delivery', $uri);
        self::assertStringContainsString('countryCode=CZ', $uri);
        self::assertStringContainsString('postalCode=14800', $uri);
        self::assertStringContainsString('cityName=Prague', $uri);
        self::assertStringContainsString('countyName=Praha%204', $uri);
        self::assertStringContainsString('strictValidation=true', $uri);
    }

    public function testOmitsOptionalQueryParametersWhenNotProvided(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('prague-pickup.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->validate(AddressValidationType::Pickup, new CountryCode('CZ'));

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringNotContainsString('postalCode=', $uri);
        self::assertStringNotContainsString('cityName=', $uri);
        self::assertStringNotContainsString('countyName=', $uri);
        self::assertStringNotContainsString('strictValidation=', $uri);
    }

    public function testHandlesAddressWithoutServiceArea(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream(
                    json_encode([
                        'address' => [[
                            'countryCode' => 'CZ',
                            'postalCode' => '14800',
                        ]],
                    ], JSON_THROW_ON_ERROR),
                ),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $result = $api->validate(AddressValidationType::Pickup, new CountryCode('CZ'));

        self::assertCount(1, $result->addresses);
        self::assertNull($result->addresses[0]->serviceArea);
        self::assertSame('', $result->addresses[0]->cityName);
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): AddressApi
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

        return new AddressApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/address/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

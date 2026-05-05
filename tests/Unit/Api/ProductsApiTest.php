<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use DateTimeImmutable;
use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\ProductsApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\DimensionUnit;
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
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class ProductsApiTest extends TestCase
{
    public function testHydratesProductsResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('cz-to-us.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $result = $this->callList($api);

        self::assertCount(3, $result->products);
        self::assertSame('EXPRESS WORLDWIDE', $result->products[0]->productName);
        self::assertSame('P', $result->products[0]->productCode);
        self::assertSame('TD', $result->products[0]->networkTypeCode);
        self::assertFalse($result->products[0]->isCustomerAgreement);
        self::assertSame('H', $result->products[2]->productCode);
        self::assertTrue($result->products[2]->isCustomerAgreement);
    }

    public function testEmitsRequiredAndOptionalQueryParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('cz-to-us.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $this->callList($api);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/products?', $uri);
        self::assertStringContainsString('accountNumber=123456789', $uri);
        self::assertStringContainsString('originCountryCode=CZ', $uri);
        self::assertStringContainsString('destinationCountryCode=US', $uri);
        self::assertStringContainsString('weight=5', $uri);
        self::assertStringContainsString('length=30', $uri);
        self::assertStringContainsString('width=20', $uri);
        self::assertStringContainsString('height=15', $uri);
        self::assertStringContainsString('plannedShippingDate=2026-05-10', $uri);
        self::assertStringContainsString('isCustomsDeclarable=true', $uri);
        self::assertStringContainsString('unitOfMeasurement=metric', $uri);
        self::assertStringContainsString('originPostalCode=14800', $uri);
        self::assertStringContainsString('destinationPostalCode=10001', $uri);
    }

    public function testReturnsEmptyResponseWhenProductsKeyMissing(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{}')),
        );

        $api = $this->makeApi($mockClient, $factory);
        $result = $this->callList($api);

        self::assertSame([], $result->products);
    }

    private function callList(ProductsApi $api): \Medzuch\DhlExpress\Dto\Product\ProductsResponse
    {
        return $api->list(
            account: new AccountNumber('123456789'),
            originCountryCode: new CountryCode('CZ'),
            destinationCountryCode: new CountryCode('US'),
            weight: new Weight(5.0, WeightUnit::KG),
            dimensions: new Dimensions(30.0, 20.0, 15.0, DimensionUnit::CM),
            plannedShippingDate: new DateTimeImmutable('2026-05-10'),
            isCustomsDeclarable: true,
            unitOfMeasurement: UnitSystem::Metric,
            originPostalCode: new PostalCode('14800'),
            originCityName: 'Prague',
            destinationPostalCode: new PostalCode('10001'),
            destinationCityName: 'New York',
        );
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): ProductsApi
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

        return new ProductsApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/products/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

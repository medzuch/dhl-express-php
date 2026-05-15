<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use DateTimeImmutable;
use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\RatesApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\Builder\RateRequestBuilder;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Dto\Rate\RateRequest;
use Medzuch\DhlExpress\Dto\Rate\RatesResponse;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateTypeCode;
use Medzuch\DhlExpress\Enum\RateCurrencyType;
use Medzuch\DhlExpress\Enum\RateNetworkTypeCode;
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

final class RatesApiTest extends TestCase
{
    public function testQuoteEmitsExpectedQueryParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream($this->loadFixture('multi-piece-quote.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $api->quote(
            account: new AccountNumber('123456789'),
            originCountryCode: new CountryCode('SG'),
            originCityName: 'Singapore',
            destinationCountryCode: new CountryCode('FR'),
            destinationCityName: 'Paris',
            weight: new Weight(1.5, WeightUnit::KG),
            dimensions: new Dimensions(10.0, 20.0, 30.0, DimensionUnit::CM),
            plannedShippingDate: new DateTimeImmutable('2026-06-01'),
            isCustomsDeclarable: true,
            unitOfMeasurement: UnitSystem::Metric,
            originPostalCode: new PostalCode('048582'),
            destinationPostalCode: new PostalCode('75001'),
            nextBusinessDay: true,
            estimatedDeliveryDateType: EstimatedDeliveryDateTypeCode::QDDC,
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();

        self::assertSame('GET', $sent->getMethod());
        self::assertStringContainsString('/rates?', $uri);
        self::assertStringContainsString('accountNumber=123456789', $uri);
        self::assertStringContainsString('originCountryCode=SG', $uri);
        self::assertStringContainsString('originCityName=Singapore', $uri);
        self::assertStringContainsString('destinationCountryCode=FR', $uri);
        self::assertStringContainsString('destinationCityName=Paris', $uri);
        self::assertStringContainsString('weight=1.5', $uri);
        self::assertStringContainsString('plannedShippingDate=2026-06-01', $uri);
        self::assertStringContainsString('isCustomsDeclarable=true', $uri);
        self::assertStringContainsString('unitOfMeasurement=metric', $uri);
        self::assertStringContainsString('nextBusinessDay=true', $uri);
        self::assertStringContainsString('estimatedDeliveryDateType=QDDC', $uri);
    }

    public function testQuoteHydratesResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream($this->loadFixture('multi-piece-quote.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $response = $api->quote(
            account: new AccountNumber('123'),
            originCountryCode: new CountryCode('SG'),
            originCityName: 'Singapore',
            destinationCountryCode: new CountryCode('FR'),
            destinationCityName: 'Paris',
            weight: new Weight(1.0, WeightUnit::KG),
            dimensions: new Dimensions(1.0, 1.0, 1.0, DimensionUnit::CM),
            plannedShippingDate: new DateTimeImmutable('2026-06-01'),
            isCustomsDeclarable: true,
            unitOfMeasurement: UnitSystem::Metric,
        );

        self::assertInstanceOf(RatesResponse::class, $response);
        self::assertCount(2, $response->products);
        $first = $response->products[0];
        self::assertSame('EXPRESS WORLDWIDE NONDOC', $first->productName);
        self::assertSame(RateNetworkTypeCode::TD, $first->networkTypeCode);
        self::assertNotNull($first->weight);
        self::assertSame(1.2, $first->weight->volumetric);
        self::assertCount(3, $first->totalPrices);
        self::assertSame(RateCurrencyType::BILLC, $first->totalPrices[0]->currencyType);
        self::assertSame(157.95, $first->totalPrices[0]->price);
        self::assertCount(1, $first->totalPriceBreakdowns);
        self::assertCount(1, $first->detailedPriceBreakdowns);
        self::assertCount(2, $first->detailedPriceBreakdowns[0]->breakdown);
        self::assertNotNull($first->deliveryCapabilities);
        self::assertSame(3, $first->deliveryCapabilities->totalTransitDays);
        self::assertNotNull($first->estimatedDeliveryDate);
        self::assertSame(EstimatedDeliveryDateTypeCode::QDDC, $first->estimatedDeliveryDate->typeCode);

        self::assertCount(1, $response->exchangeRates);
        self::assertSame(0.8638, $response->exchangeRates[0]->currentExchangeRate);
        self::assertSame(['Sample warning'], $response->warnings);
    }

    public function testQuoteManySendsJsonBodyForPostRates(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream($this->loadFixture('multi-piece-quote.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);
        $request = $this->buildSampleRateRequest();

        $response = $api->quoteMany($request);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('application/json', $sent->getHeaderLine('Content-Type'));
        self::assertStringEndsWith('/rates', (string) $sent->getUri());

        $body = (string) $sent->getBody();
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($decoded);
        self::assertSame('Prague', $decoded['customerDetails']['shipperDetails']['cityName']);
        self::assertSame('metric', $decoded['unitOfMeasurement']);
        self::assertCount(2, $decoded['packages']);

        self::assertInstanceOf(RatesResponse::class, $response);
    }

    public function testQuoteManySendsStrictValidationWhenProvided(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream($this->loadFixture('multi-piece-quote.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $api->quoteMany($this->buildSampleRateRequest(), strictValidation: true);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertStringContainsString('/rates?strictValidation=true', (string) $sent->getUri());
    }

    public function testQuoteManyOmitsStrictValidationWhenNull(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream($this->loadFixture('multi-piece-quote.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $api->quoteMany($this->buildSampleRateRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringNotContainsString('strictValidation', $uri);
        self::assertStringEndsWith('/rates', $uri);
    }

    private function buildSampleRateRequest(): RateRequest
    {
        return (new RateRequestBuilder())
            ->withShipper(new RateAddress(new CountryCode('CZ'), new PostalCode('14800'), 'Prague'))
            ->withReceiver(new RateAddress(new CountryCode('DE'), new PostalCode('10115'), 'Berlin'))
            ->withPlannedShippingDate(new DateTimeImmutable('2026-06-01T13:00:00+00:00'))
            ->withUnitSystem(UnitSystem::Metric)
            ->withIsCustomsDeclarable(false)
            ->withPackage(new RatePackage(
                weight: new Weight(2.0, WeightUnit::KG),
                dimensions: new Dimensions(10.0, 10.0, 10.0, DimensionUnit::CM),
            ))
            ->withPackage(new RatePackage(new Weight(1.5, WeightUnit::KG)))
            ->build();
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): RatesApi
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

        return new RatesApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/rates/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

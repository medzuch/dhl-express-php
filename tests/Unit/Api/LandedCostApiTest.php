<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\LandedCostApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\Builder\LandedCostRequestBuilder;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Dto\LandedCost\LineItem;
use Medzuch\DhlExpress\Dto\Rate\RatesResponse;
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
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class LandedCostApiTest extends TestCase
{
    public function testEstimatePostsJsonBodyAndHydratesResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('multi-piece-quote.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $request = (new LandedCostRequestBuilder())
            ->withShipper(new RateAddress(new CountryCode('US'), new PostalCode('90011'), 'LOS ANGELES'))
            ->withReceiver(new RateAddress(new CountryCode('AU'), new PostalCode('1021'), 'MELBOURNE'))
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withUnitSystem(UnitSystem::Metric)
            ->withCurrency(new CurrencyCode('AUD'))
            ->withIsCustomsDeclarable(true)
            ->withPackage(new RatePackage(new Weight(1.0, WeightUnit::KG)))
            ->withLineItem(new LineItem(
                number: 1,
                quantity: 1.0,
                unitPrice: 50.0,
                unitPriceCurrencyCode: new CurrencyCode('AUD'),
                manufacturerCountry: new CountryCode('CN'),
                name: 'KNITWEAR',
            ))
            ->build();

        $response = $api->estimate($request);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('application/json', $sent->getHeaderLine('Content-Type'));
        self::assertStringEndsWith('/landed-cost', (string) $sent->getUri());

        $body = (string) $sent->getBody();
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($decoded);
        self::assertSame('LOS ANGELES', $decoded['customerDetails']['shipperDetails']['cityName']);
        self::assertSame('AUD', $decoded['currencyCode']);
        self::assertCount(1, $decoded['items']);

        self::assertInstanceOf(RatesResponse::class, $response);
        self::assertCount(2, $response->products);
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): LandedCostApi
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

        return new LandedCostApi($builder, $transport);
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

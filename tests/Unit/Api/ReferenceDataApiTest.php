<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\ReferenceDataApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\ReferenceData\ReferenceDataResponse;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\ComparisonOperator;
use Medzuch\DhlExpress\Enum\ReferenceDataset;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class ReferenceDataApiTest extends TestCase
{
    public function testHydratesResponseRows(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('incoterm-dap.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $result = $api->lookup(
            ReferenceDataset::Incoterm,
            filterByValue: 'DAP',
            filterByAttribute: 'incoterm',
            comparisonOperator: ComparisonOperator::Equal,
        );

        self::assertInstanceOf(ReferenceDataResponse::class, $result);
        self::assertSame([], $result->warnings);
        self::assertCount(1, $result->referenceData);
        $entry = $result->referenceData[0];
        self::assertSame('incoterm', $entry->datasetName);
        self::assertCount(1, $entry->data);
        $row = $entry->data[0];
        self::assertCount(2, $row);
        self::assertSame('incoterm', $row[0]->attribute);
        self::assertSame('DAP', $row[0]->value);
        self::assertSame('incotermName', $row[1]->attribute);
        self::assertSame('Delivered at Place', $row[1]->value);
    }

    public function testEmitsAllOptionalParametersWhenProvided(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('incoterm-dap.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->lookup(
            ReferenceDataset::Incoterm,
            filterByValue: 'DAP',
            filterByAttribute: 'incoterm',
            comparisonOperator: ComparisonOperator::Equal,
            queryString: 'operationName:shipment:equal',
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/reference-data?', $uri);
        self::assertStringContainsString('datasetName=incoterm', $uri);
        self::assertStringContainsString('filterByValue=DAP', $uri);
        self::assertStringContainsString('filterByAttribute=incoterm', $uri);
        self::assertStringContainsString('comparisonOperator=equal', $uri);
        self::assertStringContainsString('queryString=operationName%3Ashipment%3Aequal', $uri);
    }

    public function testOmitsOptionalParametersWhenNull(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{}')),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->lookup(ReferenceDataset::Country);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('datasetName=country', $uri);
        self::assertStringNotContainsString('filterByValue=', $uri);
        self::assertStringNotContainsString('filterByAttribute=', $uri);
        self::assertStringNotContainsString('comparisonOperator=', $uri);
        self::assertStringNotContainsString('queryString=', $uri);
    }

    public function testReturnsEmptyResponseWhenNoReferenceData(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{}')),
        );

        $api = $this->makeApi($mockClient, $factory);
        $result = $api->lookup(ReferenceDataset::All);

        self::assertSame([], $result->referenceData);
        self::assertSame([], $result->warnings);
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): ReferenceDataApi
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

        return new ReferenceDataApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/reference-data/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

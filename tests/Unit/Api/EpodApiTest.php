<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\EpodApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Epod\EpodResponse;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\EpodContent;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class EpodApiTest extends TestCase
{
    public function testHydratesEpodDocumentsFromResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('single-pdf.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $result = $api->get(
            new TrackingNumber('1234567890'),
            new AccountNumber('421554708'),
        );

        self::assertInstanceOf(EpodResponse::class, $result);
        self::assertCount(1, $result->documents);
        $document = $result->documents[0];
        self::assertSame('PDF', $document->encodingFormat);
        self::assertSame('POD', $document->typeCode);
        self::assertSame('JVBERi0xLjQKJSBzYW1wbGUgYmFzZTY0IFBERg==', $document->content);
        self::assertNotEmpty($document->decodeContent());
    }

    public function testEmitsRequiredQueryParametersAndPath(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('single-pdf.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->get(
            new TrackingNumber('1234567890'),
            new AccountNumber('421554708'),
            EpodContent::DetailEsig,
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/shipments/1234567890/proof-of-delivery?', $uri);
        self::assertStringContainsString('shipperAccountNumber=421554708', $uri);
        self::assertStringContainsString('content=epod-detail-esig', $uri);
    }

    public function testOmitsContentParamWhenNotSpecified(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('single-pdf.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->get(
            new TrackingNumber('1234567890'),
            new AccountNumber('421554708'),
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringNotContainsString('content=', $uri);
    }

    public function testOmitsShipperAccountNumberWhenNotSupplied(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('single-pdf.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->get(new TrackingNumber('1234567890'));

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/shipments/1234567890/proof-of-delivery', $uri);
        self::assertStringNotContainsString('shipperAccountNumber=', $uri);
    }

    public function testHandlesEmptyDocumentsArray(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream('{"documents":[]}'),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $result = $api->get(new TrackingNumber('1234567890'), new AccountNumber('421554708'));

        self::assertSame([], $result->documents);
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): EpodApi
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

        return new EpodApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/epod/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

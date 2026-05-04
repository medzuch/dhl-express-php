<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\TrackingApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Tracking\TrackingResponse;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class TrackingApiTest extends TestCase
{
    public function testHydratesSuccessfulResponseIntoTrackingResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('single-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $result = $api->getByTrackingNumber(new TrackingNumber('9356579890'));

        self::assertInstanceOf(TrackingResponse::class, $result);
        self::assertSame('9356579890', $result->shipmentTrackingNumber);
        self::assertSame('Success', $result->status);
        self::assertSame('BUSHING', $result->description);
        self::assertCount(3, $result->events);
        self::assertSame('PU', $result->events[0]->typeCode);
        self::assertSame('Shipment picked up', $result->events[0]->description);
    }

    public function testSendsGetRequestToTheCorrectShipmentTrackingEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('single-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->getByTrackingNumber(new TrackingNumber('9356579890'));

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        self::assertSame(
            'https://express.api.dhl.com/mydhlapi/test/shipments/9356579890/tracking',
            (string) $sent->getUri(),
        );
        self::assertSame('Basic ' . base64_encode('user:pass'), $sent->getHeaderLine('Authorization'));
    }

    public function testPropagatesNotFoundFromTransport(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(404)->withBody(
                $factory->createStream('{"detail":"No shipments found"}'),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $this->expectException(DhlNotFoundException::class);

        $api->getByTrackingNumber(new TrackingNumber('0000000000'));
    }

    public function testHydratesShipmentWithoutEvents(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream(
                    json_encode([
                        'shipments' => [[
                            'shipmentTrackingNumber' => '9356579890',
                            'status' => 'Success',
                            'description' => 'BUSHING',
                        ]],
                    ], JSON_THROW_ON_ERROR),
                ),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $result = $api->getByTrackingNumber(new TrackingNumber('9356579890'));

        self::assertSame([], $result->events);
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): TrackingApi
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

        return new TrackingApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/tracking/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

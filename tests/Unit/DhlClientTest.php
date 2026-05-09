<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\TrackingApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\DhlClient;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class DhlClientTest extends TestCase
{
    public function testTrackingExposesTrackingApi(): void
    {
        $client = $this->clientWithMocks();

        self::assertInstanceOf(TrackingApi::class, $client->tracking());
    }

    public function testTrackingReturnsTheSameInstanceAcrossCalls(): void
    {
        $client = $this->clientWithMocks();

        self::assertSame($client->tracking(), $client->tracking());
    }

    public function testEndToEndTrackingCallWithInjectedMockClient(): void
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
                            'events' => [],
                        ]],
                    ], JSON_THROW_ON_ERROR),
                ),
            ),
        );
        $generator = new class () implements MessageReferenceGenerator {
            public function generate(): MessageReference
            {
                return new MessageReference('11111111-2222-4333-8444-555555555555');
            }
        };

        $client = new DhlClient(
            config: $this->config(),
            httpClient: $mockClient,
            requestFactory: $factory,
            streamFactory: $factory,
            messageReferenceGenerator: $generator,
        );

        $response = $client->tracking()->getByTrackingNumber(new TrackingNumber('9356579890'));

        self::assertSame('9356579890', $response->shipmentTrackingNumber);
        self::assertSame('Success', $response->status);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        self::assertSame(
            'https://express.api.dhl.com/mydhlapi/test/shipments/9356579890/tracking',
            (string) $sent->getUri(),
        );
    }

    private function clientWithMocks(): DhlClient
    {
        $factory = new Psr17Factory();

        return new DhlClient(
            config: $this->config(),
            httpClient: new MockClient(),
            requestFactory: $factory,
            streamFactory: $factory,
        );
    }

    private function config(): ClientConfig
    {
        return new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
        );
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use DateTimeImmutable;
use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\TrackingApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Tracking\TrackingResponse;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\TrackingLevelOfDetail;
use Medzuch\DhlExpress\Enum\TrackingView;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
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

    public function testThrowsNotFoundWhenResponseShipmentsArrayIsEmpty(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream(json_encode(['shipments' => []], JSON_THROW_ON_ERROR)),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        try {
            $api->getByTrackingNumber(new TrackingNumber('0000000000'));
            self::fail('Expected DhlNotFoundException');
        } catch (DhlNotFoundException $exception) {
            self::assertStringContainsString('0000000000', $exception->getMessage());
            self::assertSame(404, $exception->httpStatus);
        }
    }

    public function testThrowsNotFoundWhenResponseHasNoShipmentsKey(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream('{}'),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $this->expectException(DhlNotFoundException::class);

        $api->getByTrackingNumber(new TrackingNumber('0000000000'));
    }

    public function testGetManyHydratesEachShipmentInResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('multi-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $results = $api->getMany([
            new TrackingNumber('9356579890'),
            new TrackingNumber('4818240420'),
            new TrackingNumber('5584773180'),
        ]);

        self::assertCount(3, $results);
        self::assertSame('9356579890', $results[0]->shipmentTrackingNumber);
        self::assertSame('Success', $results[0]->status);
        self::assertCount(2, $results[0]->events);
        self::assertSame('4818240420', $results[1]->shipmentTrackingNumber);
        self::assertSame('OK', $results[1]->events[1]->typeCode);
        self::assertSame('5584773180', $results[2]->shipmentTrackingNumber);
        self::assertSame('Failure', $results[2]->status);
        self::assertSame([], $results[2]->events);
    }

    public function testGetManyEmitsRepeatedShipmentTrackingNumberQueryParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('multi-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->getMany([
            new TrackingNumber('9356579890'),
            new TrackingNumber('4818240420'),
        ]);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/tracking?', $uri);
        self::assertStringContainsString('shipmentTrackingNumber=9356579890', $uri);
        self::assertStringContainsString('shipmentTrackingNumber=4818240420', $uri);
        self::assertStringNotContainsString('shipmentTrackingNumber%5B', $uri);
    }

    public function testGetManyRequiresAtLeastOneTrackingNumber(): void
    {
        $factory = new Psr17Factory();
        $api = $this->makeApi(new MockClient(), $factory);

        $this->expectException(\InvalidArgumentException::class);

        $api->getMany([]);
    }

    public function testGetByTrackingNumberEmitsOptionalQueryParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('single-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->getByTrackingNumber(
            new TrackingNumber('9356579890'),
            trackingView: TrackingView::LastCheckpoint,
            levelOfDetail: TrackingLevelOfDetail::Piece,
            requestControlledAccessDataCodes: false,
            requestGMTOffsetPerEvent: true,
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/shipments/9356579890/tracking?', $uri);
        self::assertStringContainsString('trackingView=last-checkpoint', $uri);
        self::assertStringContainsString('levelOfDetail=piece', $uri);
        self::assertStringContainsString('requestControlledAccessDataCodes=false', $uri);
        self::assertStringContainsString('requestGMTOffsetPerEvent=true', $uri);
    }

    public function testGetManyEmitsOptionalQueryParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('multi-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->getMany(
            [new TrackingNumber('9356579890')],
            trackingView: TrackingView::AllCheckpointsWithRemarks,
            levelOfDetail: TrackingLevelOfDetail::All,
            requestControlledAccessDataCodes: true,
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('trackingView=all-checkpoints-with-remarks', $uri);
        self::assertStringContainsString('levelOfDetail=all', $uri);
        self::assertStringContainsString('requestControlledAccessDataCodes=true', $uri);
    }

    public function testGetManyByPieceIdEmitsRepeatedPieceTrackingNumberParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('multi-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->getManyByPieceId('JD014600004617230770', 'JD014600004617230779');

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/tracking?', $uri);
        self::assertStringContainsString('pieceTrackingNumber=JD014600004617230770', $uri);
        self::assertStringContainsString('pieceTrackingNumber=JD014600004617230779', $uri);
        self::assertStringNotContainsString('pieceTrackingNumber%5B', $uri);
    }

    public function testGetManyByPieceIdRequiresAtLeastOnePieceId(): void
    {
        $factory = new Psr17Factory();
        $api = $this->makeApi(new MockClient(), $factory);

        $this->expectException(\InvalidArgumentException::class);

        $api->getManyByPieceId();
    }

    public function testGetManyByPieceIdEnforcesMaxOf200(): void
    {
        $factory = new Psr17Factory();
        $api = $this->makeApi(new MockClient(), $factory);

        $this->expectException(\InvalidArgumentException::class);

        $api->getManyByPieceId(...array_fill(0, 201, 'JD0146000046172307'));
    }

    public function testGetManyByReferenceEmitsAllRequiredParameters(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('multi-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->getManyByReference(
            shipmentReference: 'CustomerReference1',
            shipperAccountNumber: new AccountNumber('123456789'),
            dateRangeFrom: new DateTimeImmutable('2026-05-01'),
            dateRangeTo: new DateTimeImmutable('2026-06-01'),
            shipmentReferenceType: 'CU',
            trackingView: TrackingView::ShipmentDetailsOnly,
            levelOfDetail: TrackingLevelOfDetail::Shipment,
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/tracking?', $uri);
        self::assertStringContainsString('shipmentReference=CustomerReference1', $uri);
        self::assertStringContainsString('shipperAccountNumber=123456789', $uri);
        self::assertStringContainsString('dateRangeFrom=2026-05-01', $uri);
        self::assertStringContainsString('dateRangeTo=2026-06-01', $uri);
        self::assertStringContainsString('shipmentReferenceType=CU', $uri);
        self::assertStringContainsString('trackingView=shipment-details-only', $uri);
        self::assertStringContainsString('levelOfDetail=shipment', $uri);
    }

    public function testGetManyByReferenceAcceptsPayerAccountInsteadOfShipper(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('multi-shipment.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->getManyByReference(
            shipmentReference: 'CustomerReference1',
            payerAccountNumber: new AccountNumber('987654321'),
            dateRangeFrom: new DateTimeImmutable('2026-05-01'),
            dateRangeTo: new DateTimeImmutable('2026-06-01'),
        );

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('payerAccountNumber=987654321', $uri);
        self::assertStringNotContainsString('shipperAccountNumber', $uri);
    }

    public function testGetManyByReferenceRequiresShipperOrPayerAccount(): void
    {
        $factory = new Psr17Factory();
        $api = $this->makeApi(new MockClient(), $factory);

        $this->expectException(\InvalidArgumentException::class);

        $api->getManyByReference(
            shipmentReference: 'CustomerReference1',
            dateRangeFrom: new DateTimeImmutable('2026-05-01'),
            dateRangeTo: new DateTimeImmutable('2026-06-01'),
        );
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

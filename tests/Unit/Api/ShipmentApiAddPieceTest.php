<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use DateTimeImmutable;
use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\ShipmentApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\AddPiecePackage;
use Medzuch\DhlExpress\Dto\Shipment\AddPieceRequest;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class ShipmentApiAddPieceTest extends TestCase
{
    public function testAddPieceReturnsVoidOnSuccessfulResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{}')),
        );

        $api = $this->makeApi($mockClient, $factory);

        // addPiece() returns void — we just assert no exception was thrown and a request was sent
        $api->addPiece('1234567890', $this->makeAddPieceRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
    }

    public function testAddPieceSendsPatchMethod(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{}')),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->addPiece('1234567890', $this->makeAddPieceRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('PATCH', $sent->getMethod());
    }

    public function testAddPieceSendsRequestToCorrectPath(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{}')),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->addPiece('9876543210', $this->makeAddPieceRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertStringContainsString('/shipments/9876543210/add-piece', (string) $sent->getUri());
    }

    public function testAddPieceSendsCorrectJsonBody(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{}')),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->addPiece('1234567890', $this->makeAddPieceRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $body = json_decode((string) $sent->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertSame('2026-06-15', $body['originalPlannedShippingDate']);
        self::assertSame('P', $body['productCode']);
        self::assertArrayHasKey('content', $body);
        self::assertArrayHasKey('packages', $body['content']);
    }

    private function makeAddPieceRequest(): AddPieceRequest
    {
        return new AddPieceRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-06-15'),
            productCode: 'P',
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            packages: [new AddPiecePackage(weight: 1.5)],
        );
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): ShipmentApi
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

        return new ShipmentApi($builder, $transport);
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Http;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Exception\DhlServerException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\ResponseParser;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

final class HttpTransportTest extends TestCase
{
    public function testReturnsParsedBodyForSuccessfulResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{"shipments":[{"shipmentTrackingNumber":"9356579890"}]}')),
        );
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()));

        $body = $transport->send($this->sampleRequest($factory));

        self::assertSame(['shipments' => [['shipmentTrackingNumber' => '9356579890']]], $body);
    }

    public function testForwardsRequestToTheUnderlyingClient(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200));
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()));

        $request = $this->sampleRequest($factory);
        $transport->send($request);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        self::assertSame('https://express.api.dhl.com/mydhlapi/test/shipments/9356579890/tracking', (string) $sent->getUri());
    }

    public function testWrapsClientExceptionInDhlNetworkException(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $clientException = new class ('connect timeout') extends RuntimeException implements ClientExceptionInterface {
        };
        $mockClient->addException($clientException);
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()));

        try {
            $transport->send($this->sampleRequest($factory));
            self::fail('Expected DhlNetworkException');
        } catch (DhlNetworkException $e) {
            self::assertSame($clientException, $e->getPrevious());
            self::assertStringContainsString('connect timeout', $e->getMessage());
        }
    }

    public function testPropagatesDhlApiExceptionFromParser(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(404)->withBody($factory->createStream('{"detail":"No shipments found"}')));
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()));

        try {
            $transport->send($this->sampleRequest($factory));
            self::fail('Expected DhlNotFoundException');
        } catch (DhlNotFoundException $e) {
            self::assertSame(404, $e->httpStatus);
            self::assertSame('No shipments found', $e->dhlMessage);
        }
    }

    public function testPropagatesServerExceptionFromParser(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(503)->withBody($factory->createStream('{"detail":"upstream unavailable"}')));
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()));

        $this->expectException(DhlServerException::class);

        $transport->send($this->sampleRequest($factory));
    }

    private function sampleRequest(Psr17Factory $factory): RequestInterface
    {
        return $factory->createRequest(
            'GET',
            'https://express.api.dhl.com/mydhlapi/test/shipments/9356579890/tracking',
        );
    }
}

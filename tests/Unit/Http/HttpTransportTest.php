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
use Psr\Log\AbstractLogger;
use RuntimeException;
use Stringable;

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

    public function testLogsRequestAndResponseAtDebugLevel(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($factory->createStream('{"shipments":[]}')),
        );
        $logger = new RecordingLogger();
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()), $logger);

        $request = $factory->createRequest(
            'POST',
            'https://express.api.dhl.com/mydhlapi/test/rates',
        )
            ->withHeader('Authorization', 'Basic c2VjcmV0OnBhc3N3b3Jk')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($factory->createStream('{"customerDetails":{}}'));

        $transport->send($request);

        self::assertCount(2, $logger->records);

        $requestRecord = $logger->records[0];
        self::assertSame('debug', $requestRecord['level']);
        self::assertStringContainsString('DHL request POST', $requestRecord['message']);
        self::assertSame('POST', $requestRecord['context']['method']);
        self::assertSame(
            'https://express.api.dhl.com/mydhlapi/test/rates',
            $requestRecord['context']['url'],
        );
        self::assertSame(['Basic [REDACTED]'], $requestRecord['context']['headers']['Authorization']);
        self::assertSame(['application/json'], $requestRecord['context']['headers']['Content-Type']);
        self::assertSame('{"customerDetails":{}}', $requestRecord['context']['body']);

        $responseRecord = $logger->records[1];
        self::assertSame('debug', $responseRecord['level']);
        self::assertStringContainsString('DHL response 200', $responseRecord['message']);
        self::assertSame(200, $responseRecord['context']['status']);
        self::assertSame(['application/json'], $responseRecord['context']['headers']['Content-Type']);
        self::assertSame('{"shipments":[]}', $responseRecord['context']['body']);
    }

    public function testRedactsAuthorizationHeaderRegardlessOfCasing(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200));
        $logger = new RecordingLogger();
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()), $logger);

        $request = $factory->createRequest('GET', 'https://example.test/x')
            ->withHeader('authorization', 'Basic c2VjcmV0');

        $transport->send($request);

        $headerName = array_key_exists('authorization', $logger->records[0]['context']['headers'])
            ? 'authorization'
            : 'Authorization';
        self::assertSame(
            ['Basic [REDACTED]'],
            $logger->records[0]['context']['headers'][$headerName],
        );
    }

    public function testLeavesRequestBodyReadableAfterLogging(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200));
        $logger = new RecordingLogger();
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()), $logger);

        $request = $factory->createRequest('POST', 'https://example.test/x')
            ->withBody($factory->createStream('{"foo":"bar"}'));

        $transport->send($request);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('{"foo":"bar"}', (string) $sent->getBody());
    }

    public function testLogsTransportFailureBeforeRewrappingException(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $clientException = new class ('connect timeout') extends RuntimeException implements ClientExceptionInterface {
        };
        $mockClient->addException($clientException);
        $logger = new RecordingLogger();
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()), $logger);

        try {
            $transport->send($this->sampleRequest($factory));
            self::fail('Expected DhlNetworkException');
        } catch (DhlNetworkException) {
            // expected
        }

        self::assertCount(2, $logger->records);
        self::assertStringContainsString('DHL request', $logger->records[0]['message']);

        $failureRecord = $logger->records[1];
        self::assertSame('debug', $failureRecord['level']);
        self::assertSame('DHL transport failure', $failureRecord['message']);
        self::assertSame('connect timeout', $failureRecord['context']['error']);
    }

    public function testWorksWithoutLoggerInjected(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{"ok":true}')),
        );
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()));

        $body = $transport->send($this->sampleRequest($factory));

        self::assertSame(['ok' => true], $body);
    }

    private function sampleRequest(Psr17Factory $factory): RequestInterface
    {
        return $factory->createRequest(
            'GET',
            'https://express.api.dhl.com/mydhlapi/test/shipments/9356579890/tracking',
        );
    }
}

/**
 * @internal
 */
final class RecordingLogger extends AbstractLogger
{
    /**
     * @var list<array{level: string, message: string, context: array<mixed>}>
     */
    public array $records = [];

    /**
     * @param array<mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => (string) $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Http;

use Medzuch\DhlExpress\Exception\DhlAuthenticationException;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Exception\DhlRateLimitException;
use Medzuch\DhlExpress\Exception\DhlServerException;
use Medzuch\DhlExpress\Exception\DhlValidationException;
use Medzuch\DhlExpress\Http\ResponseParser;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class ResponseParserTest extends TestCase
{
    public function testParsesSuccessfulJsonBody(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        $body = $parser->parse($this->jsonResponse(200, ['shipments' => [['shipmentTrackingNumber' => '9356579890']]]));

        self::assertSame(['shipments' => [['shipmentTrackingNumber' => '9356579890']]], $body);
    }

    public function testReturnsEmptyArrayForEmptySuccessfulBody(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        $body = $parser->parse($this->responseWithBody(200, ''));

        self::assertSame([], $body);
    }

    public function testThrowsServerExceptionWhenSuccessfulBodyIsMalformedJson(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        $this->expectException(DhlServerException::class);
        $this->expectExceptionMessage('malformed JSON');

        $parser->parse($this->responseWithBody(200, '<html>not json</html>'));
    }

    public function testThrowsValidationExceptionFor400(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        $body = [
            'detail' => '#/customerDetails/shipperDetails: required key [countryCode] not found',
            'status' => '998',
        ];

        try {
            $parser->parse($this->jsonResponse(400, $body));
            self::fail('Expected DhlValidationException');
        } catch (DhlValidationException $e) {
            self::assertSame(400, $e->httpStatus);
            self::assertSame('998', $e->dhlErrorCode);
            self::assertSame(
                '#/customerDetails/shipperDetails: required key [countryCode] not found',
                $e->dhlMessage,
            );
        }
    }

    public function testThrowsValidationExceptionFor422(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        $this->expectException(DhlValidationException::class);

        $parser->parse($this->jsonResponse(422, ['title' => 'Validation error', 'status' => '998']));
    }

    public function testThrowsAuthenticationExceptionFor401(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        $this->expectException(DhlAuthenticationException::class);

        $parser->parse($this->jsonResponse(401, ['detail' => 'invalid credentials']));
    }

    public function testThrowsNotFoundExceptionFor404(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        $this->expectException(DhlNotFoundException::class);

        $parser->parse($this->jsonResponse(404, ['detail' => 'No shipments found']));
    }

    public function testThrowsServerExceptionFor500(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        $this->expectException(DhlServerException::class);

        $parser->parse($this->jsonResponse(500, ['detail' => 'internal error']));
    }

    public function testParsesRetryAfterIntegerSecondsOn429(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        try {
            $factory = new Psr17Factory();
            $response = $factory->createResponse(429)
                ->withHeader('Retry-After', '30')
                ->withBody($factory->createStream(json_encode(['detail' => 'rate limited'], JSON_THROW_ON_ERROR)));
            $parser->parse($response);
            self::fail('Expected DhlRateLimitException');
        } catch (DhlRateLimitException $e) {
            self::assertSame(30, $e->retryAfter);
        }
    }

    public function testRetryAfterIsNullWhenHeaderMissingOn429(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        try {
            $parser->parse($this->jsonResponse(429, ['detail' => 'rate limited']));
            self::fail('Expected DhlRateLimitException');
        } catch (DhlRateLimitException $e) {
            self::assertNull($e->retryAfter);
        }
    }

    public function testRetryAfterIsNullWhenHeaderIsNonInteger(): void
    {
        // HTTP-date variant of Retry-After per RFC 7231 — we don't parse it
        $parser = new ResponseParser(new DhlErrorMapper());
        $factory = new Psr17Factory();

        $response = $factory->createResponse(429)
            ->withHeader('Retry-After', 'Wed, 21 Oct 2026 07:28:00 GMT')
            ->withBody($factory->createStream(''));

        try {
            $parser->parse($response);
            self::fail('Expected DhlRateLimitException');
        } catch (DhlRateLimitException $e) {
            self::assertNull($e->retryAfter);
        }
    }

    public function testStillRoutesByStatusWhenErrorBodyIsMalformedJson(): void
    {
        $parser = new ResponseParser(new DhlErrorMapper());

        try {
            $parser->parse($this->responseWithBody(404, '<html>not json</html>'));
            self::fail('Expected DhlNotFoundException');
        } catch (DhlNotFoundException $e) {
            self::assertSame(404, $e->httpStatus);
            self::assertNull($e->dhlErrorCode);
            self::assertNull($e->dhlMessage);
        }
    }

    /**
     * @param array<string, mixed> $body
     */
    private function jsonResponse(int $status, array $body): ResponseInterface
    {
        return $this->responseWithBody($status, json_encode($body, JSON_THROW_ON_ERROR));
    }

    private function responseWithBody(int $status, string $body): ResponseInterface
    {
        $factory = new Psr17Factory();

        return $factory->createResponse($status)
            ->withBody($factory->createStream($body));
    }
}

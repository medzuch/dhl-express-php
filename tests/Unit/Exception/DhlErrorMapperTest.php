<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Exception;

use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlAuthenticationException;
use Medzuch\DhlExpress\Exception\DhlAuthorizationException;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Exception\DhlRateLimitException;
use Medzuch\DhlExpress\Exception\DhlServerException;
use Medzuch\DhlExpress\Exception\DhlValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DhlErrorMapperTest extends TestCase
{
    /**
     * @return iterable<string, array{int, class-string<DhlApiException>}>
     */
    public static function statusToExceptionProvider(): iterable
    {
        yield 'auth 401' => [401, DhlAuthenticationException::class];
        yield 'auth 403' => [403, DhlAuthorizationException::class];
        yield 'validation 400' => [400, DhlValidationException::class];
        yield 'validation 422' => [422, DhlValidationException::class];
        yield 'not found 404' => [404, DhlNotFoundException::class];
        yield 'rate limit 429' => [429, DhlRateLimitException::class];
        yield 'server 500' => [500, DhlServerException::class];
        yield 'server 502' => [502, DhlServerException::class];
        yield 'server 503' => [503, DhlServerException::class];
        yield 'unmapped 418' => [418, DhlApiException::class];
        yield 'unmapped 409' => [409, DhlApiException::class];
    }

    /**
     * @param class-string<DhlApiException> $expectedClass
     */
    #[DataProvider('statusToExceptionProvider')]
    public function testRoutesByHttpStatus(int $status, string $expectedClass): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map($status, []);

        self::assertInstanceOf($expectedClass, $exception);
        self::assertSame($status, $exception->httpStatus);
    }

    public function testFallbackForUnmappedStatusReturnsBaseDhlApiException(): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map(418, []);

        // Fallback must not be one of the specialized subclasses.
        self::assertNotInstanceOf(DhlAuthenticationException::class, $exception);
        self::assertNotInstanceOf(DhlAuthorizationException::class, $exception);
        self::assertNotInstanceOf(DhlValidationException::class, $exception);
        self::assertNotInstanceOf(DhlNotFoundException::class, $exception);
        self::assertNotInstanceOf(DhlRateLimitException::class, $exception);
        self::assertNotInstanceOf(DhlServerException::class, $exception);
        self::assertSame(DhlApiException::class, $exception::class);
    }

    public function testCarriesDhlErrorCodeAndMessageFromResponseBody(): void
    {
        $body = [
            'instance' => '/expressapi/shipments',
            'detail' => '#/customerDetails/shipperDetails: required key [countryCode] not found',
            'title' => 'Validation error',
            'message' => 'Unprocessable Entity',
            'status' => '998',
        ];

        $mapper = new DhlErrorMapper();
        $exception = $mapper->map(422, $body);

        self::assertSame(422, $exception->httpStatus);
        self::assertSame('998', $exception->dhlErrorCode);
        self::assertSame(
            '#/customerDetails/shipperDetails: required key [countryCode] not found',
            $exception->dhlMessage,
        );
        self::assertSame($body, $exception->responseBody);
    }

    public function testFallsBackToTitleWhenDetailIsMissing(): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map(400, ['title' => 'Validation error', 'status' => '998']);

        self::assertSame('Validation error', $exception->dhlMessage);
    }

    public function testFallsBackToMessageWhenDetailAndTitleAreMissing(): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map(400, ['message' => 'Unprocessable Entity']);

        self::assertSame('Unprocessable Entity', $exception->dhlMessage);
    }

    public function testRateLimitCarriesRetryAfterFromMapperCall(): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map(429, ['detail' => 'Too many requests'], 30);

        self::assertInstanceOf(DhlRateLimitException::class, $exception);
        self::assertSame(30, $exception->retryAfter);
    }

    public function testRateLimitRetryAfterIsNullWhenNotProvided(): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map(429, []);

        self::assertInstanceOf(DhlRateLimitException::class, $exception);
        self::assertNull($exception->retryAfter);
    }

    public function testHandlesEmptyBodyGracefully(): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map(500, []);

        self::assertNull($exception->dhlErrorCode);
        self::assertNull($exception->dhlMessage);
        self::assertSame([], $exception->responseBody);
    }

    public function testExceptionMessageIncludesStatusAndDhlDetail(): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map(422, [
            'detail' => 'required key [countryCode] not found',
            'status' => '998',
        ]);

        self::assertStringContainsString('422', $exception->getMessage());
        self::assertStringContainsString('998', $exception->getMessage());
        self::assertStringContainsString('required key [countryCode] not found', $exception->getMessage());
    }

    public function testIgnoresNonStringStatusInBody(): void
    {
        $mapper = new DhlErrorMapper();

        $exception = $mapper->map(400, ['status' => 998]);

        self::assertNull($exception->dhlErrorCode);
    }
}

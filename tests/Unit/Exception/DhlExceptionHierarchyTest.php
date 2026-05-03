<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Exception;

use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlAuthenticationException;
use Medzuch\DhlExpress\Exception\DhlAuthorizationException;
use Medzuch\DhlExpress\Exception\DhlException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Exception\DhlRateLimitException;
use Medzuch\DhlExpress\Exception\DhlServerException;
use Medzuch\DhlExpress\Exception\DhlValidationException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class DhlExceptionHierarchyTest extends TestCase
{
    public function testBaseExceptionIsAbstractAndExtendsRuntimeException(): void
    {
        $reflection = new ReflectionClass(DhlException::class);

        self::assertTrue($reflection->isAbstract(), 'DhlException must be abstract');
        self::assertTrue($reflection->isSubclassOf(RuntimeException::class));
    }

    public function testNetworkExceptionExtendsBaseDirectly(): void
    {
        $previous = new RuntimeException('connect timeout');
        $exception = new DhlNetworkException('Failed to reach DHL', $previous);

        self::assertInstanceOf(DhlException::class, $exception);
        self::assertSame('Failed to reach DHL', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testApiExceptionCarriesHttpStatusDhlCodeMessageAndBody(): void
    {
        $exception = new DhlApiException(
            message: 'Bad request from DHL',
            httpStatus: 400,
            dhlErrorCode: '7012',
            dhlMessage: 'Paperless Trade not allowed',
            responseBody: ['detail' => 'PLT not enabled'],
        );

        self::assertInstanceOf(DhlException::class, $exception);
        self::assertSame(400, $exception->httpStatus);
        self::assertSame('7012', $exception->dhlErrorCode);
        self::assertSame('Paperless Trade not allowed', $exception->dhlMessage);
        self::assertSame(['detail' => 'PLT not enabled'], $exception->responseBody);
    }

    public function testApiExceptionDefaultsAreEmpty(): void
    {
        $exception = new DhlApiException(
            message: 'unknown',
            httpStatus: 418,
        );

        self::assertNull($exception->dhlErrorCode);
        self::assertNull($exception->dhlMessage);
        self::assertSame([], $exception->responseBody);
    }

    /**
     * @return iterable<string, array{class-string<DhlApiException>, int}>
     */
    public static function statusSubclassProvider(): iterable
    {
        yield 'authentication 401' => [DhlAuthenticationException::class, 401];
        yield 'authorization 403' => [DhlAuthorizationException::class, 403];
        yield 'validation 400' => [DhlValidationException::class, 400];
        yield 'validation 422' => [DhlValidationException::class, 422];
        yield 'not found 404' => [DhlNotFoundException::class, 404];
        yield 'rate limit 429' => [DhlRateLimitException::class, 429];
        yield 'server 500' => [DhlServerException::class, 500];
        yield 'server 503' => [DhlServerException::class, 503];
    }

    /**
     * @param class-string<DhlApiException> $class
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('statusSubclassProvider')]
    public function testStatusSpecificSubclassesExtendApiException(string $class, int $status): void
    {
        $exception = new $class(
            message: 'sample',
            httpStatus: $status,
        );

        self::assertInstanceOf(DhlApiException::class, $exception);
        self::assertInstanceOf(DhlException::class, $exception);
        self::assertSame($status, $exception->httpStatus);
    }
}

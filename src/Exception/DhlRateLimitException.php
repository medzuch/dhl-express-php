<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

use Throwable;

/**
 * Thrown for HTTP 429 Too Many Requests — caller exceeded DHL's
 * rate limit.
 *
 * `$retryAfter` carries the integer-seconds value of the response's
 * `Retry-After` header when present. The HTTP-date variant of that
 * header (RFC 7231) is not parsed here; in practice DHL sends an
 * integer-seconds delay, and a `null` retryAfter means either the
 * header was absent or its value was not a parsable integer.
 */
final class DhlRateLimitException extends DhlApiException
{
    /**
     * @param array<string, mixed> $responseBody
     */
    public function __construct(
        string $message,
        int $httpStatus,
        ?string $dhlErrorCode = null,
        ?string $dhlMessage = null,
        array $responseBody = [],
        ?Throwable $previous = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $httpStatus, $dhlErrorCode, $dhlMessage, $responseBody, $previous);
    }
}

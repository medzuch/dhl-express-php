<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

/**
 * Thrown for HTTP 5xx — DHL-side failure (gateway, internal error,
 * service unavailable, etc.). Generally retryable.
 */
final class DhlServerException extends DhlApiException
{
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

/**
 * Thrown for HTTP 429 Too Many Requests — caller exceeded DHL's
 * rate limit. The response may include a Retry-After hint.
 */
final class DhlRateLimitException extends DhlApiException
{
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

/**
 * Thrown for HTTP 401 Unauthorized — invalid or missing credentials.
 */
final class DhlAuthenticationException extends DhlApiException
{
}

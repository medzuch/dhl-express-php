<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

use Throwable;

/**
 * Thrown when no HTTP response was received: connection refused,
 * DNS failure, TLS handshake error, read timeout, etc.
 */
final class DhlNetworkException extends DhlException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

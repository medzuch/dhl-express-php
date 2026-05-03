<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

/**
 * Thrown for HTTP 403 Forbidden — credentials valid but the account
 * is not allowed to perform the requested operation.
 */
final class DhlAuthorizationException extends DhlApiException
{
}

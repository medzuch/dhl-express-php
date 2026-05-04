<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

/**
 * Thrown for HTTP 400 Bad Request and 422 Unprocessable Entity —
 * request reached DHL but failed schema or semantic validation.
 */
final class DhlValidationException extends DhlApiException
{
}

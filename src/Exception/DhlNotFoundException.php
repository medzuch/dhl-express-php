<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

/**
 * Thrown for HTTP 404 Not Found — the requested resource (tracking
 * number, shipment, pickup, etc.) does not exist.
 */
final class DhlNotFoundException extends DhlApiException
{
}

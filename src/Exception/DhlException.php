<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

use RuntimeException;

/**
 * Base type for everything thrown by this library.
 *
 * Consumers can `catch (DhlException $e)` to handle any failure
 * originating from a DHL API call without leaking transport-level
 * exceptions from the underlying HTTP client.
 */
abstract class DhlException extends RuntimeException
{
}

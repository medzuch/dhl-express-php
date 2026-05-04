<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

use Throwable;

/**
 * Thrown when DHL returns an HTTP response but the call was not
 * successful. Status-specific subclasses ({@see DhlAuthenticationException},
 * {@see DhlValidationException}, etc.) refine the meaning, but consumers
 * can also `catch (DhlApiException $e)` to handle any responded failure
 * uniformly.
 *
 * `dhlErrorCode` and `dhlMessage` are surfaced from the response body
 * when the server provides them; `responseBody` carries the parsed JSON
 * body for diagnostic purposes.
 *
 * @phpstan-type ResponseBody array<string, mixed>
 */
class DhlApiException extends DhlException
{
    /**
     * @param ResponseBody $responseBody
     */
    public function __construct(
        string $message,
        public readonly int $httpStatus,
        public readonly ?string $dhlErrorCode = null,
        public readonly ?string $dhlMessage = null,
        public readonly array $responseBody = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}

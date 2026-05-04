<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

/**
 * Pure mapper from an HTTP error response into a {@see DhlApiException}
 * subclass.
 *
 * Routing is by HTTP status only; the DHL-internal error code travels
 * inside the exception as `$dhlErrorCode` for callers that want to
 * branch on it. The DHL error response shape is documented in
 * `dhl_openapi.yaml::supermodelIoLogisticsExpressErrorResponse`.
 *
 * @phpstan-type ResponseBody array<string, mixed>
 */
final class DhlErrorMapper
{
    /**
     * @param ResponseBody $body
     */
    public function map(int $httpStatus, array $body): DhlApiException
    {
        $dhlErrorCode = $this->extractDhlErrorCode($body);
        $dhlMessage = $this->extractDhlMessage($body);
        $message = $this->buildExceptionMessage($httpStatus, $dhlErrorCode, $dhlMessage);

        $class = match (true) {
            $httpStatus === 401 => DhlAuthenticationException::class,
            $httpStatus === 403 => DhlAuthorizationException::class,
            $httpStatus === 404 => DhlNotFoundException::class,
            $httpStatus === 429 => DhlRateLimitException::class,
            $httpStatus === 400, $httpStatus === 422 => DhlValidationException::class,
            $httpStatus >= 500 && $httpStatus < 600 => DhlServerException::class,
            default => DhlApiException::class,
        };

        return new $class(
            message: $message,
            httpStatus: $httpStatus,
            dhlErrorCode: $dhlErrorCode,
            dhlMessage: $dhlMessage,
            responseBody: $body,
        );
    }

    /**
     * @param ResponseBody $body
     */
    private function extractDhlErrorCode(array $body): ?string
    {
        $status = $body['status'] ?? null;

        return is_string($status) ? $status : null;
    }

    /**
     * @param ResponseBody $body
     */
    private function extractDhlMessage(array $body): ?string
    {
        foreach (['detail', 'title', 'message'] as $key) {
            $value = $body[$key] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function buildExceptionMessage(int $httpStatus, ?string $dhlErrorCode, ?string $dhlMessage): string
    {
        $parts = ["DHL API HTTP {$httpStatus}"];

        if ($dhlErrorCode !== null) {
            $parts[] = "(code {$dhlErrorCode})";
        }

        if ($dhlMessage !== null) {
            $parts[] = ': ' . $dhlMessage;
        }

        return implode(' ', $parts);
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Http;

use JsonException;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Exception\DhlServerException;
use Psr\Http\Message\ResponseInterface;

/**
 * Turns a PSR-7 response into either a parsed JSON body array
 * (on success) or an appropriate {@see DhlApiException} subclass
 * (on failure).
 *
 * Routing rule:
 *   - 2xx with valid JSON  → array
 *   - 2xx with empty body  → []
 *   - 2xx with non-JSON    → throw {@see DhlServerException}
 *   - non-2xx              → defer to {@see DhlErrorMapper}; if the
 *                            body fails to parse we still route by
 *                            HTTP status, with an empty body.
 */
final class ResponseParser
{
    public function __construct(private readonly DhlErrorMapper $errorMapper)
    {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws DhlApiException
     */
    public function parse(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $rawBody = (string) $response->getBody();

        if ($status >= 200 && $status < 300) {
            return $this->decodeSuccessBody($status, $rawBody);
        }

        $body = $this->tryDecode($rawBody);

        throw $this->errorMapper->map($status, $body);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeSuccessBody(int $status, string $rawBody): array
    {
        if ($rawBody === '') {
            return [];
        }

        try {
            return $this->decode($rawBody);
        } catch (JsonException $e) {
            throw new DhlServerException(
                message: "DHL returned malformed JSON for HTTP {$status}: {$e->getMessage()}",
                httpStatus: $status,
                previous: $e,
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function tryDecode(string $rawBody): array
    {
        if ($rawBody === '') {
            return [];
        }

        try {
            return $this->decode($rawBody);
        } catch (JsonException) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function decode(string $rawBody): array
    {
        /** @var mixed $decoded */
        $decoded = json_decode($rawBody, true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new JsonException('Expected a JSON object at the response root');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}

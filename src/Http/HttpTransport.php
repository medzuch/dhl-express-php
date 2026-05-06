<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Http;

use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Sends a built request through the injected PSR-18 client and runs
 * the response through {@see ResponseParser}.
 *
 * Catches the PSR-18 transport-level exception and rewraps it as
 * {@see DhlNetworkException} so consumers never see raw Guzzle / cURL
 * exceptions.
 *
 * The optional PSR-3 logger emits one `debug` line for the outgoing
 * request and one for the response (status + headers + body). The
 * `Authorization` header is always redacted before logging so basic
 * auth credentials never leak. Callers wanting silent transport pass
 * nothing — {@see NullLogger} is the default and is a free no-op.
 */
final class HttpTransport
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly ResponseParser $parser,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws DhlApiException
     * @throws DhlNetworkException
     */
    public function send(RequestInterface $request): array
    {
        $this->logRequest($request);

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logger->debug('DHL transport failure', ['error' => $e->getMessage()]);

            throw new DhlNetworkException(
                'HTTP transport failure while calling DHL: ' . $e->getMessage(),
                $e,
            );
        }

        $this->logResponse($response);

        return $this->parser->parse($response);
    }

    private function logRequest(RequestInterface $request): void
    {
        $this->logger->debug(
            sprintf('DHL request %s %s', $request->getMethod(), (string) $request->getUri()),
            [
                'method' => $request->getMethod(),
                'url' => (string) $request->getUri(),
                'headers' => $this->redactHeaders($request),
                'body' => $this->bodyAsString($request),
            ],
        );
    }

    private function logResponse(ResponseInterface $response): void
    {
        $this->logger->debug(
            sprintf('DHL response %d', $response->getStatusCode()),
            [
                'status' => $response->getStatusCode(),
                'headers' => $this->redactHeaders($response),
                'body' => $this->bodyAsString($response),
            ],
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private function redactHeaders(MessageInterface $message): array
    {
        $headers = [];
        foreach ($message->getHeaders() as $name => $values) {
            $key = (string) $name;
            if (strcasecmp($key, 'Authorization') === 0) {
                $headers[$key] = ['Basic [REDACTED]'];
                continue;
            }

            $headers[$key] = array_values($values);
        }

        return $headers;
    }

    private function bodyAsString(MessageInterface $message): string
    {
        $body = $message->getBody();
        $contents = (string) $body;

        if ($body->isSeekable()) {
            $body->rewind();
        }

        return $contents;
    }
}

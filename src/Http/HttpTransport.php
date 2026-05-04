<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Http;

use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Sends a built request through the injected PSR-18 client and runs
 * the response through {@see ResponseParser}.
 *
 * Catches the PSR-18 transport-level exception and rewraps it as
 * {@see DhlNetworkException} so consumers never see raw Guzzle / cURL
 * exceptions.
 */
final class HttpTransport
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ResponseParser $parser,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws DhlApiException
     * @throws DhlNetworkException
     */
    public function send(RequestInterface $request): array
    {
        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new DhlNetworkException(
                'HTTP transport failure while calling DHL: ' . $e->getMessage(),
                $e,
            );
        }

        return $this->parser->parse($response);
    }
}

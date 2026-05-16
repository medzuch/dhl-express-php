<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Http;

use JsonException;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\ValueObject\IntegrationProfile;
use Medzuch\DhlExpress\ValueObject\PlatformIdentifier;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

/**
 * Assembles a PSR-7 {@see RequestInterface} for a single DHL API call.
 *
 * Concerns:
 * - Joins the configured base URL with the relative path.
 * - URL-encodes query parameters.
 * - Serializes a JSON body when provided.
 * - Stamps the standard headers (Authorization, Accept,
 *   Accept-Language, Message-Reference, x-version) plus the optional
 *   3PV identification headers from {@see IntegrationProfile}.
 *
 * The HTTP transport is intentionally elsewhere — this class produces
 * requests but never sends them.
 */
final readonly class RequestBuilder
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly MessageReferenceGenerator $messageReferenceGenerator,
        private readonly ClientConfig $config,
    ) {
    }

    /**
     * @param array<string, scalar|list<scalar>>|null $queryParams
     * @param array<string, mixed>|null               $jsonBody
     */
    public function build(
        string $method,
        string $path,
        ?array $queryParams = null,
        ?array $jsonBody = null,
    ): RequestInterface {
        $url = $this->buildUrl($path, $queryParams);

        $request = $this->requestFactory->createRequest($method, $url);
        $request = $this->applyStandardHeaders($request);
        $request = $this->applyIntegrationProfileHeaders($request, $this->config->integrationProfile);

        if ($jsonBody !== null) {
            $request = $this->applyJsonBody($request, $jsonBody);
        }

        return $request;
    }

    /**
     * @param array<string, scalar|list<scalar>>|null $queryParams
     */
    private function buildUrl(string $path, ?array $queryParams): string
    {
        $url = rtrim($this->config->baseUrl(), '/') . '/' . ltrim($path, '/');

        if ($queryParams === null || $queryParams === []) {
            return $url;
        }

        $parts = [];
        foreach ($queryParams as $key => $value) {
            $encodedKey = rawurlencode($key);
            if (is_array($value)) {
                foreach ($value as $item) {
                    $parts[] = $encodedKey . '=' . rawurlencode((string) $item);
                }
            } else {
                $parts[] = $encodedKey . '=' . rawurlencode((string) $value);
            }
        }

        return $url . '?' . implode('&', $parts);
    }

    private function applyStandardHeaders(RequestInterface $request): RequestInterface
    {
        return $request
            ->withHeader('Authorization', $this->config->credentials->toBasicAuthHeader())
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Language', $this->config->acceptLanguage)
            ->withHeader('Message-Reference', $this->messageReferenceGenerator->generate()->value)
            ->withHeader('x-version', $this->config->xVersion);
    }

    private function applyIntegrationProfileHeaders(
        RequestInterface $request,
        ?IntegrationProfile $profile,
    ): RequestInterface {
        if ($profile === null) {
            return $request;
        }

        if ($profile->plugin !== null) {
            $request = $this->applyPlatformIdentifier(
                $request,
                $profile->plugin,
                'Plugin-Name',
                'Plugin-Version',
            );
        }

        if ($profile->shippingSystem !== null) {
            $request = $this->applyPlatformIdentifier(
                $request,
                $profile->shippingSystem,
                'Shipping-System-Platform-Name',
                'Shipping-System-Platform-Version',
            );
        }

        if ($profile->webstore !== null) {
            $request = $this->applyPlatformIdentifier(
                $request,
                $profile->webstore,
                'Webstore-Platform-Name',
                'Webstore-Platform-Version',
            );
        }

        return $request;
    }

    private function applyPlatformIdentifier(
        RequestInterface $request,
        PlatformIdentifier $identifier,
        string $nameHeader,
        string $versionHeader,
    ): RequestInterface {
        return $request
            ->withHeader($nameHeader, $identifier->name)
            ->withHeader($versionHeader, $identifier->version);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function applyJsonBody(RequestInterface $request, array $body): RequestInterface
    {
        try {
            $payload = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to encode request body as JSON: ' . $e->getMessage(), 0, $e);
        }

        $stream = $this->streamFactory->createStream($payload);

        return $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }
}

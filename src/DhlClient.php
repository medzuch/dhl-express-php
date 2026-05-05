<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Medzuch\DhlExpress\Api\IdentifierApi;
use Medzuch\DhlExpress\Api\TrackingApi;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RandomMessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Top-level entry point for the DHL Express API.
 *
 * The facade is purely compositional — it owns no business logic of
 * its own, only delegating to one `*Api` per DHL domain.
 *
 * The PSR-18 client and PSR-17 factories are optional constructor
 * parameters — Guzzle is used by default, but any compliant
 * implementation can be injected. The same applies to
 * {@see MessageReferenceGenerator}, exposed primarily as a test seam.
 */
final class DhlClient
{
    private readonly TrackingApi $tracking;
    private readonly IdentifierApi $identifier;

    public function __construct(
        ClientConfig $config,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?MessageReferenceGenerator $messageReferenceGenerator = null,
    ) {
        $guzzleFactory = new HttpFactory();
        $resolvedRequestFactory = $requestFactory ?? $guzzleFactory;
        $resolvedStreamFactory = $streamFactory ?? $guzzleFactory;
        $resolvedHttpClient = $httpClient ?? new GuzzleClient(['timeout' => $config->timeout]);
        $resolvedGenerator = $messageReferenceGenerator ?? new RandomMessageReferenceGenerator();

        $requestBuilder = new RequestBuilder(
            $resolvedRequestFactory,
            $resolvedStreamFactory,
            $resolvedGenerator,
            $config,
        );
        $transport = new HttpTransport($resolvedHttpClient, new ResponseParser(new DhlErrorMapper()));

        $this->tracking = new TrackingApi($requestBuilder, $transport);
        $this->identifier = new IdentifierApi($requestBuilder, $transport);
    }

    public function tracking(): TrackingApi
    {
        return $this->tracking;
    }

    public function identifier(): IdentifierApi
    {
        return $this->identifier;
    }
}

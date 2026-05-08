<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Medzuch\DhlExpress\Api\AddressApi;
use Medzuch\DhlExpress\Api\EpodApi;
use Medzuch\DhlExpress\Api\IdentifierApi;
use Medzuch\DhlExpress\Api\LandedCostApi;
use Medzuch\DhlExpress\Api\ProductsApi;
use Medzuch\DhlExpress\Api\RatesApi;
use Medzuch\DhlExpress\Api\ReferenceDataApi;
use Medzuch\DhlExpress\Api\ServicePointApi;
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
use Psr\Log\LoggerInterface;

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
 *
 * Pass a PSR-3 logger to capture request and response payloads at
 * `debug` level (Authorization header redacted). Without a logger
 * the transport stays silent.
 */
final class DhlClient
{
    private readonly TrackingApi $tracking;
    private readonly IdentifierApi $identifier;
    private readonly AddressApi $address;
    private readonly ProductsApi $products;
    private readonly ReferenceDataApi $referenceData;
    private readonly EpodApi $epod;
    private readonly ServicePointApi $servicePoints;
    private readonly RatesApi $rates;
    private readonly LandedCostApi $landedCost;

    public function __construct(
        ClientConfig $config,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?MessageReferenceGenerator $messageReferenceGenerator = null,
        ?LoggerInterface $logger = null,
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
        $transport = new HttpTransport(
            $resolvedHttpClient,
            new ResponseParser(new DhlErrorMapper()),
            $logger,
        );

        $this->tracking = new TrackingApi($requestBuilder, $transport);
        $this->identifier = new IdentifierApi($requestBuilder, $transport);
        $this->address = new AddressApi($requestBuilder, $transport);
        $this->products = new ProductsApi($requestBuilder, $transport);
        $this->referenceData = new ReferenceDataApi($requestBuilder, $transport);
        $this->epod = new EpodApi($requestBuilder, $transport);
        $this->servicePoints = new ServicePointApi($requestBuilder, $transport);
        $this->rates = new RatesApi($requestBuilder, $transport);
        $this->landedCost = new LandedCostApi($requestBuilder, $transport);
    }

    public function tracking(): TrackingApi
    {
        return $this->tracking;
    }

    public function identifier(): IdentifierApi
    {
        return $this->identifier;
    }

    public function address(): AddressApi
    {
        return $this->address;
    }

    public function products(): ProductsApi
    {
        return $this->products;
    }

    public function referenceData(): ReferenceDataApi
    {
        return $this->referenceData;
    }

    public function epod(): EpodApi
    {
        return $this->epod;
    }

    public function servicePoints(): ServicePointApi
    {
        return $this->servicePoints;
    }

    public function rates(): RatesApi
    {
        return $this->rates;
    }

    public function landedCost(): LandedCostApi
    {
        return $this->landedCost;
    }
}

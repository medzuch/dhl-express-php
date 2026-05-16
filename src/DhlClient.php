<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Medzuch\DhlExpress\Api\AddressApi;
use Medzuch\DhlExpress\Api\EarlyShipmentScreeningApi;
use Medzuch\DhlExpress\Api\EpodApi;
use Medzuch\DhlExpress\Api\IdentifierApi;
use Medzuch\DhlExpress\Api\InvoiceApi;
use Medzuch\DhlExpress\Api\LandedCostApi;
use Medzuch\DhlExpress\Api\PickupApi;
use Medzuch\DhlExpress\Api\ProductsApi;
use Medzuch\DhlExpress\Api\RatesApi;
use Medzuch\DhlExpress\Api\ReferenceDataApi;
use Medzuch\DhlExpress\Api\ServicePointApi;
use Medzuch\DhlExpress\Api\ShipmentApi;
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
 * parameters — `php-http/discovery` auto-detects a PSR-18 client
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
    private readonly ShipmentApi $shipments;
    private readonly PickupApi $pickups;
    private readonly InvoiceApi $invoices;
    private readonly EarlyShipmentScreeningApi $earlyShipmentScreening;

    public function __construct(
        ClientConfig $config,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?MessageReferenceGenerator $messageReferenceGenerator = null,
        ?LoggerInterface $logger = null,
    ) {
        $resolvedRequestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $resolvedStreamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $resolvedHttpClient = $httpClient ?? Psr18ClientDiscovery::find();
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
        $this->shipments = new ShipmentApi($requestBuilder, $transport);
        $this->pickups = new PickupApi($requestBuilder, $transport);
        $this->invoices = new InvoiceApi($requestBuilder, $transport);
        $this->earlyShipmentScreening = new EarlyShipmentScreeningApi($requestBuilder, $transport);
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

    public function shipments(): ShipmentApi
    {
        return $this->shipments;
    }

    public function pickups(): PickupApi
    {
        return $this->pickups;
    }

    public function invoices(): InvoiceApi
    {
        return $this->invoices;
    }

    public function earlyShipmentScreening(): EarlyShipmentScreeningApi
    {
        return $this->earlyShipmentScreening;
    }
}

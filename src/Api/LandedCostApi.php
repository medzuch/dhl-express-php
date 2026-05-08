<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\LandedCost\LandedCostRequest;
use Medzuch\DhlExpress\Dto\Rate\RatesResponse;
use Medzuch\DhlExpress\Dto\Rate\RatesResponseHydrator;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;

/**
 * Landed-cost endpoint.
 *
 * `POST /landed-cost` returns DHL's duty / tax / freight estimate
 * for the supplied items. The response shape is the same
 * `supermodelIoLogisticsExpressRates` model the `/rates` endpoints
 * return — line-item-level breakdowns surface inside each
 * {@see \Medzuch\DhlExpress\Dto\Rate\QuotedProduct::$rawItems}.
 */
final class LandedCostApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
        private readonly RatesResponseHydrator $hydrator = new RatesResponseHydrator(),
    ) {
    }

    /**
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function estimate(LandedCostRequest $request): RatesResponse
    {
        $httpRequest = $this->requestBuilder->build(
            'POST',
            '/landed-cost',
            jsonBody: $request->toArray(),
        );

        $body = $this->transport->send($httpRequest);

        return $this->hydrator->hydrate($body);
    }
}

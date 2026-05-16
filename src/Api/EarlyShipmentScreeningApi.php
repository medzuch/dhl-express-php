<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\EarlyShipmentScreening\EarlyShipmentScreeningRequest;
use Medzuch\DhlExpress\Dto\EarlyShipmentScreening\EarlyShipmentScreeningResponse;
use Medzuch\DhlExpress\Dto\EarlyShipmentScreening\EarlyShipmentScreeningResponseHydrator;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;

/**
 * Early-shipment-screening endpoint.
 *
 * Targets `POST /early-shipment-screening` to run Denied Party
 * screening on Breakbulk (BBX) 'baby' shipments before committing to
 * a full create-shipment call. Helps surface shipments that need
 * compliance investigation early without delaying the rest.
 */
final readonly class EarlyShipmentScreeningApi
{
    public function __construct(
        private RequestBuilder $requestBuilder,
        private HttpTransport $transport,
        private EarlyShipmentScreeningResponseHydrator $hydrator = new EarlyShipmentScreeningResponseHydrator(),
    ) {
    }

    /**
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function screen(EarlyShipmentScreeningRequest $request): EarlyShipmentScreeningResponse
    {
        $httpRequest = $this->requestBuilder->build(
            'POST',
            '/early-shipment-screening',
            jsonBody: $request->toArray(),
        );

        $body = $this->transport->send($httpRequest);

        return $this->hydrator->hydrate($body);
    }
}

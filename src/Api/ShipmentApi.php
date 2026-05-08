<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentRequest;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentResponse;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentResponseHydrator;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;

/**
 * Shipment-creation endpoint.
 *
 * Phase 4a ships only {@see self::create()} — the happy-path flow for
 * a domestic non-customs shipment. PLT (uploadImage / uploadInvoiceData
 * / getImage), `addPiece`, and customs-declarable shipments arrive in
 * Phases 4b and 4c.
 */
final class ShipmentApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
        private readonly CreateShipmentResponseHydrator $hydrator = new CreateShipmentResponseHydrator(),
    ) {
    }

    /**
     * Create a shipment via `POST /shipments`.
     *
     * Pass `validateDataOnly=true` to ask DHL to validate the request
     * without actually creating a shipment. Useful for integration
     * tests against the sandbox so the suite doesn't accumulate real
     * shipments.
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function create(
        CreateShipmentRequest $request,
        bool $validateDataOnly = false,
    ): CreateShipmentResponse {
        $queryParams = $validateDataOnly ? ['validateDataOnly' => 'true'] : null;

        $httpRequest = $this->requestBuilder->build(
            'POST',
            '/shipments',
            queryParams: $queryParams,
            jsonBody: $request->toArray(),
        );

        $body = $this->transport->send($httpRequest);

        return $this->hydrator->hydrate($body);
    }
}

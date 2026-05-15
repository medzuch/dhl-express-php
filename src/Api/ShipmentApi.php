<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\Shipment\AddPieceRequest;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentRequest;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentResponse;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentResponseHydrator;
use Medzuch\DhlExpress\Dto\Shipment\GetImageRequest;
use Medzuch\DhlExpress\Dto\Shipment\GetImageResponse;
use Medzuch\DhlExpress\Dto\Shipment\GetImageResponseHydrator;
use Medzuch\DhlExpress\Dto\Shipment\UploadImageRequest;
use Medzuch\DhlExpress\Dto\Shipment\UploadInvoiceDataRequest;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;

/**
 * Shipment endpoint.
 *
 * Phase 4a ships {@see self::create()} — the happy-path flow for a
 * domestic non-customs shipment.
 *
 * Phase 4b adds:
 * - {@see self::uploadImage()} — `PATCH /shipments/{id}/upload-image`
 *   for Paperless Trade (PLT) document images.
 * - {@see self::uploadInvoiceData()} — `PATCH /shipments/{id}/upload-invoice-data`
 *   for structured customs invoice data.
 * - {@see self::getImage()} — `GET /shipments/{id}/get-image`
 *   to retrieve uploaded document images.
 *
 * Phase 4c adds:
 * - {@see self::addPiece()} — `PATCH /shipments/{id}/add-piece`
 *   to attach extra pieces to a previously created shipment.
 */
final class ShipmentApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
        private readonly CreateShipmentResponseHydrator $hydrator = new CreateShipmentResponseHydrator(),
        private readonly GetImageResponseHydrator $imageHydrator = new GetImageResponseHydrator(),
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

    /**
     * Upload Paperless Trade (PLT) document images to an existing shipment.
     *
     * `PATCH /shipments/{shipmentTrackingNumber}/upload-image`
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function uploadImage(TrackingNumber $shipmentTrackingNumber, UploadImageRequest $request): void
    {
        $httpRequest = $this->requestBuilder->build(
            'PATCH',
            "/shipments/{$shipmentTrackingNumber}/upload-image",
            jsonBody: $request->toArray(),
        );

        $this->transport->send($httpRequest);
    }

    /**
     * Upload structured customs invoice data to an existing shipment.
     *
     * `PATCH /shipments/{shipmentTrackingNumber}/upload-invoice-data`
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function uploadInvoiceData(TrackingNumber $shipmentTrackingNumber, UploadInvoiceDataRequest $request): void
    {
        $httpRequest = $this->requestBuilder->build(
            'PATCH',
            "/shipments/{$shipmentTrackingNumber}/upload-invoice-data",
            jsonBody: $request->toArray(),
        );

        $this->transport->send($httpRequest);
    }

    /**
     * Add extra pieces to a previously created shipment via
     * `PATCH /shipments/{shipmentTrackingNumber}/add-piece`.
     *
     * Returns void — the 200 response has no meaningful body.
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function addPiece(TrackingNumber $shipmentTrackingNumber, AddPieceRequest $request): void
    {
        $httpRequest = $this->requestBuilder->build(
            'PATCH',
            "/shipments/{$shipmentTrackingNumber}/add-piece",
            jsonBody: $request->toArray(),
        );

        $this->transport->send($httpRequest);
    }

    /**
     * Retrieve document images from an existing shipment.
     *
     * `GET /shipments/{shipmentTrackingNumber}/get-image`
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function getImage(TrackingNumber $shipmentTrackingNumber, GetImageRequest $request): GetImageResponse
    {
        $httpRequest = $this->requestBuilder->build(
            'GET',
            "/shipments/{$shipmentTrackingNumber}/get-image",
            queryParams: $request->toQueryParams(),
        );

        $body = $this->transport->send($httpRequest);

        return $this->imageHydrator->hydrate($body);
    }
}

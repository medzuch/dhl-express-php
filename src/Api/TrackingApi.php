<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use DateTimeImmutable;
use InvalidArgumentException;
use Medzuch\DhlExpress\Dto\Tracking\ShipmentEvent;
use Medzuch\DhlExpress\Dto\Tracking\TrackingResponse;
use Medzuch\DhlExpress\Enum\TrackingLevelOfDetail;
use Medzuch\DhlExpress\Enum\TrackingView;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Support\HydrationHelper;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;

/**
 * Tracking domain endpoints.
 *
 * - {@see self::getByTrackingNumber()} hits
 *   `GET /shipments/{shipmentTrackingNumber}/tracking` for a single
 *   shipment lookup.
 * - {@see self::getMany()} hits `GET /tracking` for up to 200
 *   shipment tracking numbers in a single call.
 * - {@see self::getManyByPieceId()} uses the same endpoint with
 *   `pieceTrackingNumber` query params for per-piece lookup.
 * - {@see self::getManyByReference()} performs the reference-based
 *   lookup (`shipmentReference` + account + date range).
 *
 * All four operations support the same response-shaping options
 * (`trackingView`, `levelOfDetail`, `requestControlledAccessDataCodes`).
 * {@see self::getByTrackingNumber()} additionally exposes
 * `requestGMTOffsetPerEvent`.
 */
final readonly class TrackingApi
{
    private const MULTI_TRACKING_LIMIT = 200;

    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
    ) {
    }

    /**
     * Fetches the tracking history for a single shipment.
     *
     * @throws DhlNotFoundException when DHL has no record of the tracking number
     *                              (either a 404 response or a 200 with an empty
     *                              `shipments` array)
     * @throws DhlApiException     for other DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function getByTrackingNumber(
        TrackingNumber $trackingNumber,
        ?TrackingView $trackingView = null,
        ?TrackingLevelOfDetail $levelOfDetail = null,
        ?bool $requestControlledAccessDataCodes = null,
        ?bool $requestGMTOffsetPerEvent = null,
    ): TrackingResponse {
        $queryParams = $this->buildViewParams(
            $trackingView,
            $levelOfDetail,
            $requestControlledAccessDataCodes,
        );
        if ($requestGMTOffsetPerEvent !== null) {
            $queryParams['requestGMTOffsetPerEvent'] = $requestGMTOffsetPerEvent ? 'true' : 'false';
        }

        $request = $this->requestBuilder->build(
            'GET',
            '/shipments/' . $trackingNumber->value . '/tracking',
            queryParams: $queryParams !== [] ? $queryParams : null,
        );

        $body = $this->transport->send($request);
        $shipments = $this->hydrateShipments($body);

        if ($shipments === []) {
            throw new DhlNotFoundException(
                message: sprintf('No shipment found for tracking number %s.', $trackingNumber->value),
                httpStatus: 404,
            );
        }

        return $shipments[0];
    }

    /**
     * Fetches tracking histories for up to 200 shipments in a single call.
     *
     * @param list<TrackingNumber> $trackingNumbers
     *
     * @return list<TrackingResponse>
     *
     * @throws InvalidArgumentException when called with no tracking numbers or more than 200
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function getMany(
        array $trackingNumbers,
        ?TrackingView $trackingView = null,
        ?TrackingLevelOfDetail $levelOfDetail = null,
        ?bool $requestControlledAccessDataCodes = null,
    ): array {
        if ($trackingNumbers === []) {
            throw new InvalidArgumentException('getMany() requires at least one tracking number.');
        }

        if (count($trackingNumbers) > self::MULTI_TRACKING_LIMIT) {
            throw new InvalidArgumentException(
                'getMany() supports at most ' . self::MULTI_TRACKING_LIMIT . ' tracking numbers per call.',
            );
        }

        $queryParams = [
            'shipmentTrackingNumber' => array_map(
                static fn (TrackingNumber $n): string => $n->value,
                $trackingNumbers,
            ),
        ];
        $queryParams += $this->buildViewParams(
            $trackingView,
            $levelOfDetail,
            $requestControlledAccessDataCodes,
        );

        return $this->sendMultiTracking($queryParams);
    }

    /**
     * Fetches tracking histories looked up by piece tracking number(s).
     *
     * @return list<TrackingResponse>
     *
     * @throws InvalidArgumentException when called with no piece IDs or more than 200
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function getManyByPieceId(
        string ...$pieceTrackingNumbers,
    ): array {
        if ($pieceTrackingNumbers === []) {
            throw new InvalidArgumentException('getManyByPieceId() requires at least one piece tracking number.');
        }

        if (count($pieceTrackingNumbers) > self::MULTI_TRACKING_LIMIT) {
            throw new InvalidArgumentException(
                'getManyByPieceId() supports at most ' . self::MULTI_TRACKING_LIMIT . ' piece tracking numbers per call.',
            );
        }

        return $this->sendMultiTracking([
            'pieceTrackingNumber' => array_values($pieceTrackingNumbers),
        ]);
    }

    /**
     * Fetches tracking histories looked up by shipment reference.
     *
     * DHL requires the date range and either a shipper or payer account
     * alongside the reference; everything else is optional.
     *
     * @return list<TrackingResponse>
     *
     * @throws InvalidArgumentException when neither shipper nor payer account is supplied
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function getManyByReference(
        string $shipmentReference,
        DateTimeImmutable $dateRangeFrom,
        DateTimeImmutable $dateRangeTo,
        ?AccountNumber $shipperAccountNumber = null,
        ?AccountNumber $payerAccountNumber = null,
        ?string $shipmentReferenceType = null,
        ?TrackingView $trackingView = null,
        ?TrackingLevelOfDetail $levelOfDetail = null,
        ?bool $requestControlledAccessDataCodes = null,
    ): array {
        if ($shipperAccountNumber === null && $payerAccountNumber === null) {
            throw new InvalidArgumentException(
                'getManyByReference() requires either a shipper or payer account number.',
            );
        }

        $queryParams = [
            'shipmentReference' => $shipmentReference,
            'dateRangeFrom' => $dateRangeFrom->format('Y-m-d'),
            'dateRangeTo' => $dateRangeTo->format('Y-m-d'),
        ];
        if ($shipperAccountNumber !== null) {
            $queryParams['shipperAccountNumber'] = $shipperAccountNumber->value;
        }
        if ($payerAccountNumber !== null) {
            $queryParams['payerAccountNumber'] = $payerAccountNumber->value;
        }
        if ($shipmentReferenceType !== null) {
            $queryParams['shipmentReferenceType'] = $shipmentReferenceType;
        }
        $queryParams += $this->buildViewParams(
            $trackingView,
            $levelOfDetail,
            $requestControlledAccessDataCodes,
        );

        return $this->sendMultiTracking($queryParams);
    }

    /**
     * @return array<string, string>
     */
    private function buildViewParams(
        ?TrackingView $trackingView,
        ?TrackingLevelOfDetail $levelOfDetail,
        ?bool $requestControlledAccessDataCodes,
    ): array {
        $params = [];
        if ($trackingView !== null) {
            $params['trackingView'] = $trackingView->value;
        }
        if ($levelOfDetail !== null) {
            $params['levelOfDetail'] = $levelOfDetail->value;
        }
        if ($requestControlledAccessDataCodes !== null) {
            $params['requestControlledAccessDataCodes'] = $requestControlledAccessDataCodes ? 'true' : 'false';
        }

        return $params;
    }

    /**
     * @param array<string, string|list<string>> $queryParams
     *
     * @return list<TrackingResponse>
     *
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    private function sendMultiTracking(array $queryParams): array
    {
        $request = $this->requestBuilder->build(
            'GET',
            '/tracking',
            queryParams: $queryParams,
        );

        $body = $this->transport->send($request);

        return $this->hydrateShipments($body);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return list<TrackingResponse>
     */
    private function hydrateShipments(array $body): array
    {
        $shipments = $body['shipments'] ?? null;
        if (!is_array($shipments)) {
            return [];
        }

        $results = [];
        foreach ($shipments as $shipment) {
            if (!is_array($shipment)) {
                continue;
            }

            /** @var array<string, mixed> $shipment */
            $results[] = new TrackingResponse(
                shipmentTrackingNumber: HydrationHelper::stringField($shipment, 'shipmentTrackingNumber'),
                status: HydrationHelper::stringField($shipment, 'status'),
                description: HydrationHelper::stringField($shipment, 'description'),
                events: $this->hydrateEvents($shipment['events'] ?? null),
            );
        }

        return $results;
    }

    /**
     * @return list<ShipmentEvent>
     */
    private function hydrateEvents(mixed $rawEvents): array
    {
        if (!is_array($rawEvents)) {
            return [];
        }

        $events = [];
        foreach ($rawEvents as $rawEvent) {
            if (!is_array($rawEvent)) {
                continue;
            }

            /** @var array<string, mixed> $rawEvent */
            $events[] = new ShipmentEvent(
                date: HydrationHelper::stringField($rawEvent, 'date'),
                time: HydrationHelper::stringField($rawEvent, 'time'),
                typeCode: HydrationHelper::stringField($rawEvent, 'typeCode'),
                description: HydrationHelper::stringField($rawEvent, 'description'),
            );
        }

        return $events;
    }
}

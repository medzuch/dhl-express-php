<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use InvalidArgumentException;
use Medzuch\DhlExpress\Dto\Tracking\ShipmentEvent;
use Medzuch\DhlExpress\Dto\Tracking\TrackingResponse;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Support\HydrationHelper;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;

/**
 * Tracking domain endpoints.
 *
 * Two operations: {@see self::getByTrackingNumber()} for the single
 * `GET /shipments/{shipmentTrackingNumber}/tracking` lookup and
 * {@see self::getMany()} for the multi-shipment `GET /tracking`
 * endpoint (up to 200 tracking numbers per call). The richer DTO
 * surface — shipper/receiver details, piece events, etc. — lands
 * in Phase 3b.
 */
final class TrackingApi
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
     * @throws DhlApiException for other DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function getByTrackingNumber(TrackingNumber $trackingNumber): TrackingResponse
    {
        $request = $this->requestBuilder->build(
            'GET',
            '/shipments/' . $trackingNumber->value . '/tracking',
        );

        $body = $this->transport->send($request);

        return $this->hydrateShipments($body)[0] ?? new TrackingResponse(
            shipmentTrackingNumber: '',
            status: '',
            description: '',
            events: [],
        );
    }

    /**
     * Fetches tracking histories for up to 200 shipments in a single call.
     *
     * @return list<TrackingResponse>
     *
     * @throws InvalidArgumentException when called with no tracking numbers or more than 200
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function getMany(TrackingNumber ...$trackingNumbers): array
    {
        if ($trackingNumbers === []) {
            throw new InvalidArgumentException('getMany() requires at least one tracking number.');
        }

        if (count($trackingNumbers) > self::MULTI_TRACKING_LIMIT) {
            throw new InvalidArgumentException(
                'getMany() supports at most ' . self::MULTI_TRACKING_LIMIT . ' tracking numbers per call.',
            );
        }

        $values = array_values(array_map(static fn (TrackingNumber $n): string => $n->value, $trackingNumbers));

        $request = $this->requestBuilder->build(
            'GET',
            '/tracking',
            queryParams: ['shipmentTrackingNumber' => $values],
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

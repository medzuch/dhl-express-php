<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\Tracking\ShipmentEvent;
use Medzuch\DhlExpress\Dto\Tracking\TrackingResponse;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;

/**
 * Tracking domain endpoints.
 *
 * Phase 1 only ships {@see self::getByTrackingNumber()} which targets
 * `GET /shipments/{shipmentTrackingNumber}/tracking`. The multi-shipment
 * `/tracking` endpoint and the richer DTO surface land in Phase 3.
 */
final class TrackingApi
{
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

        return $this->hydrateFirstShipment($body);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function hydrateFirstShipment(array $body): TrackingResponse
    {
        $shipments = $body['shipments'] ?? null;
        $shipment = [];
        if (is_array($shipments) && isset($shipments[0]) && is_array($shipments[0])) {
            /** @var array<string, mixed> $shipment */
            $shipment = $shipments[0];
        }

        return new TrackingResponse(
            shipmentTrackingNumber: $this->stringField($shipment, 'shipmentTrackingNumber'),
            status: $this->stringField($shipment, 'status'),
            description: $this->stringField($shipment, 'description'),
            events: $this->hydrateEvents($shipment['events'] ?? null),
        );
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

            $events[] = new ShipmentEvent(
                date: $this->stringField($rawEvent, 'date'),
                time: $this->stringField($rawEvent, 'time'),
                typeCode: $this->stringField($rawEvent, 'typeCode'),
                description: $this->stringField($rawEvent, 'description'),
            );
        }

        return $events;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function stringField(array $source, string $key): string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : '';
    }
}

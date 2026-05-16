<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Depth at which the tracking endpoint emits events.
 *
 * Controls the `levelOfDetail` query parameter on both
 * `GET /shipments/{shipmentTrackingNumber}/tracking` and
 * `GET /tracking`. DHL defaults to `shipment` when omitted;
 * use `piece` for per-package events on multi-piece shipments
 * and `all` to receive both levels in the same response.
 *
 * Source: `parameters.trackingLevelOfDetail` in
 * `specs/dhl/dhl_openapi.yaml` (lines 9713-9724).
 */
enum TrackingLevelOfDetail: string
{
    case Shipment = 'shipment';
    case Piece = 'piece';
    case All = 'all';
}

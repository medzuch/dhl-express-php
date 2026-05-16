<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Slice of the tracking record the caller wants returned.
 *
 * Controls the `trackingView` query parameter on both
 * `GET /shipments/{shipmentTrackingNumber}/tracking` and
 * `GET /tracking`. DHL defaults to `all-checkpoints` when the
 * parameter is omitted; pick `last-checkpoint` for a cheap
 * status poll, `shipment-details-only` to skip events
 * entirely, or `bbx-children` for break-bulk children.
 *
 * Source: `parameters.trackingView` in `specs/dhl/dhl_openapi.yaml`
 * (lines 9725-9739).
 */
enum TrackingView: string
{
    case AllCheckpoints = 'all-checkpoints';
    case AllCheckpointsWithRemarks = 'all-checkpoints-with-remarks';
    case LastCheckpoint = 'last-checkpoint';
    case ShipmentDetailsOnly = 'shipment-details-only';
    case AdvanceShipment = 'advance-shipment';
    case BbxChildren = 'bbx-children';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Mode of transport for a landed-cost calculation.
 *
 * Source: `supermodelIoLogisticsExpressLandedCostRequest.transportationMode`
 * in `docs/dhl/dhl_openapi.yaml`.
 */
enum TransportationMode: string
{
    case Air = 'air';
    case Ocean = 'ocean';
    case Ground = 'ground';
}

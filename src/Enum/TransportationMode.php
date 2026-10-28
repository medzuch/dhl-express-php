<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Mode of transport for a landed-cost calculation.
 *
 * Source: `supermodelIoLogisticsExpressLandedCostRequest.transportationMode`
 * in `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`.
 */
enum TransportationMode: string
{
    case Air = 'air';
    case Ocean = 'ocean';
    case Ground = 'ground';
}

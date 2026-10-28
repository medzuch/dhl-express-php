<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Whether a shipment is being sent for commercial (B2B) or personal
 * (B2C) reasons. Influences duty/tax calculation in landed-cost.
 *
 * Source: `supermodelIoLogisticsExpressLandedCostRequest.shipmentPurpose`
 * in `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`.
 */
enum ShipmentPurpose: string
{
    case Commercial = 'commercial';
    case Personal = 'personal';
}

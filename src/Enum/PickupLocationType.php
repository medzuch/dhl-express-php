<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type of location from which a DHL Express pickup will be collected.
 *
 * Source: `supermodelIoLogisticsExpressPickupRequest.locationType`
 * in `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`.
 */
enum PickupLocationType: string
{
    case Business = 'business';
    case Residence = 'residence';
}

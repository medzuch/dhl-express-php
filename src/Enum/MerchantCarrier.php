<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Carrier the merchant has selected for the shipment, used by the
 * landed-cost calculator to scope its rules.
 *
 * Source: `supermodelIoLogisticsExpressLandedCostRequest.merchantSelectedCarrierName`
 * in `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`.
 */
enum MerchantCarrier: string
{
    case DHL = 'DHL';
    case UPS = 'UPS';
    case FEDEX = 'FEDEX';
    case TNT = 'TNT';
    case POST = 'POST';
    case Others = 'OTHERS';
}

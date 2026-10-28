<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Quantity classification for a line item — `prt` (parts) or `box`.
 *
 * Source: `supermodelIoLogisticsExpressLandedCostRequest.items[].quantityType`
 * in `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`.
 */
enum LineItemQuantityType: string
{
    case Part = 'prt';
    case Box = 'box';
}

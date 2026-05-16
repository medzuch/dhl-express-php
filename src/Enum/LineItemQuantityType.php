<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Quantity classification for a line item — `prt` (parts) or `box`.
 *
 * Source: `supermodelIoLogisticsExpressLandedCostRequest.items[].quantityType`
 * in `specs/dhl/dhl_openapi.yaml`.
 */
enum LineItemQuantityType: string
{
    case Part = 'prt';
    case Box = 'box';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type of additional charge included in a Landed Cost calculation.
 *
 * Source: `supermodelIoLogisticsExpressLandedCostRequest.charges[].typeCode`
 * in `specs/dhl/dhl_openapi.yaml`.
 */
enum ChargeTypeCode: string
{
    case Freight = 'freight';
    case Additional = 'additional';
    case Insurance = 'insurance';
}

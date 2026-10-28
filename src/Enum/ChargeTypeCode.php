<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type of additional charge included in a Landed Cost calculation.
 *
 * Source: `supermodelIoLogisticsExpressLandedCostRequest.charges[].typeCode`
 * in `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`.
 */
enum ChargeTypeCode: string
{
    case Freight = 'freight';
    case Additional = 'additional';
    case Insurance = 'insurance';
}

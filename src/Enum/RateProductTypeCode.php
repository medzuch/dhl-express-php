<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Filter for the kinds of products to include in a rate response.
 *
 * Source: `supermodelIoLogisticsExpressRateRequest.productTypeCode`
 * in `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`.
 */
enum RateProductTypeCode: string
{
    case All = 'all';
    case DayDefinite = 'dayDefinite';
    case TimeDefinite = 'timeDefinite';
}

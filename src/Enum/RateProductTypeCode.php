<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Filter for the kinds of products to include in a rate response.
 *
 * Source: `supermodelIoLogisticsExpressRateRequest.productTypeCode`
 * in `docs/dhl/dhl_openapi.yaml`.
 */
enum RateProductTypeCode: string
{
    case All = 'all';
    case DayDefinite = 'dayDefinite';
    case TimeDefinite = 'timeDefinite';
}

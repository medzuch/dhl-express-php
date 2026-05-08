<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Whether a quoted product belongs to DHL's Day Definite (DD) or
 * Time Definite (TD) network.
 *
 * Source: `supermodelIoLogisticsExpressRates.products[].networkTypeCode`
 * in `docs/dhl/dhl_openapi.yaml`.
 */
enum RateNetworkTypeCode: string
{
    case DD = 'DD';
    case TD = 'TD';
}

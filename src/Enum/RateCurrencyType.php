<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Which currency a quoted price is expressed in: the customer's
 * billing currency (BILLC), the country's public-rates currency
 * (PULCL), or DHL's base currency (BASEC).
 *
 * Source: `supermodelIoLogisticsExpressRates.products[].totalPrice[].currencyType`
 * in `docs/dhl/dhl_openapi.yaml`.
 */
enum RateCurrencyType: string
{
    case BILLC = 'BILLC';
    case PULCL = 'PULCL';
    case BASEC = 'BASEC';
}

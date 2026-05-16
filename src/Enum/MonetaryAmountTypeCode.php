<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type tag attached to a monetary amount in a rate request — what
 * the value represents from DHL's perspective.
 *
 * Source: the `typeCode` enum nested in
 * `supermodelIoLogisticsExpressRateRequest.monetaryAmount` in
 * `specs/dhl/dhl_openapi.yaml`.
 */
enum MonetaryAmountTypeCode: string
{
    case DeclaredValue = 'declaredValue';
    case InsuredValue = 'insuredValue';
}

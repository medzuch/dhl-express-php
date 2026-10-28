<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Role a DHL Express account plays in a rate or shipment request.
 *
 * Source: `supermodelIoLogisticsExpressAccount.typeCode` enum in
 * `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`. The `duties-taxes` wire value carries
 * a hyphen, so the case name uses camelCase to stay valid PHP.
 */
enum AccountTypeCode: string
{
    case Shipper = 'shipper';
    case Payer = 'payer';
    case DutiesTaxes = 'duties-taxes';
}

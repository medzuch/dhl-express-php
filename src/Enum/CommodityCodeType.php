<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Direction for a commodity (HS) code on an export line item.
 *
 * Matches the `commodityCodes[].typeCode` enum in
 * `specs/dhl/dhl_openapi.yaml`. Used inside
 * {@see \Medzuch\DhlExpress\Dto\Shipment\CommodityCode}.
 */
enum CommodityCodeType: string
{
    case Outbound = 'outbound';
    case Inbound = 'inbound';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Business party type code applied to a shipment counterparty.
 *
 * The six values defined in the Reference Data Guide section 8 —
 * Business, Direct Consumer, Government, Other, Private, Reseller.
 * Several of these are US-only when the ultimate consignee is
 * selected as a Business Party Role; that constraint belongs in a
 * builder cross-field check, not the enum itself.
 */
enum BusinessPartyTypeCode: string
{
    case BU = 'BU';
    case DC = 'DC';
    case GV = 'GV';
    case OT = 'OT';
    case PR = 'PR';
    case RE = 'RE';
}

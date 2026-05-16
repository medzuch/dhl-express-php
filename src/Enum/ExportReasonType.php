<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Export reason type.
 *
 * Matches the `exportReasonType` field enum in
 * `specs/dhl/dhl_openapi.yaml`. Describes the reason a shipment is
 * being exported — whether it is a permanent transfer of ownership, a
 * temporary export for repair, a gift, a sample, and so on.
 */
enum ExportReasonType: string
{
    case Permanent = 'permanent';
    case Temporary = 'temporary';
    case Return = 'return';
    case UsedExhibitionGoodsToOrigin = 'used_exhibition_goods_to_origin';
    case IntercompanyUse = 'intercompany_use';
    case Commercial = 'commercial_purpose_or_sale';
    case Personal = 'personal_belongings_or_personal_use';
    case Sample = 'sample';
    case Gift = 'gift';
    case ReturnToOrigin = 'return_to_origin';
    case WarrantyReplacement = 'warranty_replacement';
    case DiplomaticGoods = 'diplomatic_goods';
    case DefenceMaterial = 'defence_material';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Service code that DHL applies when a shipment carries dangerous
 * goods.
 *
 * The 16 unique service codes referenced from the `dangerousGoods`
 * sheet of `dhl_reference_data.xlsx` — each pairs with one or more
 * {@see DangerousGoodsContentId} entries (e.g. HY covers biological
 * substances 650 and 651, HU is the umbrella for the various "not
 * restricted" classifications, HA/HB cover sodium-ion batteries,
 * YN covers the tail-lift truck service for EV battery shipments).
 */
enum DangerousGoodsServiceCode: string
{
    case HY = 'HY';
    case HL = 'HL';
    case HN = 'HN';
    case HU = 'HU';
    case HX = 'HX';
    case HC = 'HC';
    case HH = 'HH';
    case HE = 'HE';
    case HK = 'HK';
    case HW = 'HW';
    case HM = 'HM';
    case HD = 'HD';
    case HV = 'HV';
    case HA = 'HA';
    case HB = 'HB';
    case YN = 'YN';
}

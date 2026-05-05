<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Reference type code attached to an individual package on a
 * shipment.
 *
 * 81 codes drawn from the `customerPackageReferenceType` sheet of
 * `dhl_reference_data.xlsx`. The xlsx repeats most codes once per
 * `applicableCountryCode` — the enum carries the unique typeCode
 * set; per-country applicability is a builder/runtime concern.
 *
 * Distinct from {@see InvoiceReferenceTypeCode} (invoice-level) and
 * {@see LineItemReferenceTypeCode} (line-item-level) — the value
 * space is its own. CU (consignor reference) is the DHL default.
 */
enum PackageReferenceTypeCode: string
{
    case AAJ = 'AAJ';
    case AAM = 'AAM';
    case AAO = 'AAO';
    case AAP = 'AAP';
    case ABT = 'ABT';
    case ABW = 'ABW';
    case ACL = 'ACL';
    case ACR = 'ACR';
    case ACS = 'ACS';
    case ADA = 'ADA';
    case AES = 'AES';
    case AFD = 'AFD';
    case AFE = 'AFE';
    case AHX = 'AHX';
    case ALX = 'ALX';
    case ANT = 'ANT';
    case BKN = 'BKN';
    case BOL = 'BOL';
    case BRD = 'BRD';
    case CDN = 'CDN';
    case CFR = 'CFR';
    case CID = 'CID';
    case CN = 'CN';
    case CO = 'CO';
    case COD = 'COD';
    case CR = 'CR';
    case CRN = 'CRN';
    case CTN = 'CTN';
    case CU = 'CU';
    case DGC = 'DGC';
    case DOM = 'DOM';
    case DSC = 'DSC';
    case DTC = 'DTC';
    case DTM = 'DTM';
    case DTQ = 'DTQ';
    case DTR = 'DTR';
    case FF = 'FF';
    case FN = 'FN';
    case FOR = 'FOR';
    case FTR = 'FTR';
    case GRS = 'GRS';
    case HWB = 'HWB';
    case IBC = 'IBC';
    case IME = 'IME';
    case INB = 'INB';
    case IPP = 'IPP';
    case ITN = 'ITN';
    case ITR = 'ITR';
    case LLR = 'LLR';
    case MAB = 'MAB';
    case MAK = 'MAK';
    case MAT = 'MAT';
    case MRN = 'MRN';
    case MWB = 'MWB';
    case NET = 'NET';
    case NLR = 'NLR';
    case OBC = 'OBC';
    case OED = 'OED';
    case OET = 'OET';
    case OID = 'OID';
    case OOR = 'OOR';
    case PAN = 'PAN';
    case PD = 'PD';
    case PON = 'PON';
    case PRN = 'PRN';
    case RMA = 'RMA';
    case RTL = 'RTL';
    case SE = 'SE';
    case SID = 'SID';
    case SME = 'SME';
    case SON = 'SON';
    case SP = 'SP';
    case SRN = 'SRN';
    case SS = 'SS';
    case STD = 'STD';
    case SWN = 'SWN';
    case UCI = 'UCI';
    case UCN = 'UCN';
    case USG = 'USG';
    case USM = 'USM';
    case WLK = 'WLK';
}

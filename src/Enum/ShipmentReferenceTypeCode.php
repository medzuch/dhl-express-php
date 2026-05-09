<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express shipment-level reference type codes.
 *
 * Sourced from the `customerShipmentReferenceType` sheet of
 * `dhl_reference_data.xlsx` (64 unique codes). The backing value is
 * the exact wire code.
 *
 * Used in shipment creation and add-piece requests to associate
 * external reference numbers (PO numbers, RMA numbers, BOLs, etc.)
 * with a shipment.
 *
 * All codes are valid PHP identifiers so case names mirror wire values.
 */
enum ShipmentReferenceTypeCode: string
{
    case LCQ = 'LCQ';
    case BOE = 'BOE';
    case PBE = 'PBE';
    case MRN = 'MRN';
    case MWB = 'MWB';
    case NET = 'NET';
    case OBC = 'OBC';
    case OID = 'OID';
    case PD = 'PD';
    case PON = 'PON';
    case PRN = 'PRN';
    case RMA = 'RMA';
    case RTL = 'RTL';
    case SID = 'SID';
    case SP = 'SP';
    case SRN = 'SRN';
    case SS = 'SS';
    case STD = 'STD';
    case SWN = 'SWN';
    case UCI = 'UCI';
    case UCN = 'UCN';
    case AAP = 'AAP';
    case AHX = 'AHX';
    case WLK = 'WLK';
    case AAJ = 'AAJ';
    case AAM = 'AAM';
    case AAO = 'AAO';
    case ABT = 'ABT';
    case ACL = 'ACL';
    case ACR = 'ACR';
    case ACS = 'ACS';
    case ADA = 'ADA';
    case AFD = 'AFD';
    case AFE = 'AFE';
    case ANT = 'ANT';
    case BKN = 'BKN';
    case BOL = 'BOL';
    case CDN = 'CDN';
    case CID = 'CID';
    case CN = 'CN';
    case CO = 'CO';
    case COD = 'COD';
    case CR = 'CR';
    case CRN = 'CRN';
    case CTN = 'CTN';
    case CU = 'CU';
    case DGC = 'DGC';
    case DSC = 'DSC';
    case FF = 'FF';
    case FN = 'FN';
    case GRS = 'GRS';
    case HWB = 'HWB';
    case IBC = 'IBC';
    case ITN = 'ITN';
    case LLR = 'LLR';
    case MAB = 'MAB';
    case AFM = 'AFM';
    case MR1 = 'MR1';
    case MR2 = 'MR2';
    case MR3 = 'MR3';
    case MR4 = 'MR4';
    case MR5 = 'MR5';
    case MR6 = 'MR6';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Reference type code allowed at the invoice level.
 *
 * Mirrors the OpenAPI invoice-reference enum and Reference Data
 * Guide section 9 — 41 codes ranging from common identifiers
 * (PON purchase order, HWB house waybill, MRN movement reference)
 * to specialised customs and operational references. Multiple MRN
 * entries are allowed at this level per the reference note.
 */
enum InvoiceReferenceTypeCode: string
{
    case ACL = 'ACL';
    case CID = 'CID';
    case CN = 'CN';
    case CU = 'CU';
    case ITN = 'ITN';
    case UCN = 'UCN';
    case MRN = 'MRN';
    case OID = 'OID';
    case PON = 'PON';
    case RMA = 'RMA';
    case AAM = 'AAM';
    case ABT = 'ABT';
    case ADA = 'ADA';
    case AES = 'AES';
    case AFD = 'AFD';
    case ANT = 'ANT';
    case BKN = 'BKN';
    case BOL = 'BOL';
    case CDN = 'CDN';
    case COD = 'COD';
    case DSC = 'DSC';
    case FF = 'FF';
    case FN = 'FN';
    case FTR = 'FTR';
    case HWB = 'HWB';
    case IBC = 'IBC';
    case IPP = 'IPP';
    case LLR = 'LLR';
    case MAB = 'MAB';
    case MWB = 'MWB';
    case OBC = 'OBC';
    case PD = 'PD';
    case PRN = 'PRN';
    case RTL = 'RTL';
    case SID = 'SID';
    case SS = 'SS';
    case SWN = 'SWN';
    case TAR = 'TAR';
    case TCO = 'TCO';
    case PX = 'PX';
    case PTA = 'PTA';
}

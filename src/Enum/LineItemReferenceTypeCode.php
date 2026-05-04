<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Reference type code allowed at the line-item level.
 *
 * Mirrors the OpenAPI line-item-reference enum and Reference Data
 * Guide section 11 — 43 codes covering ECCN, DDTC fields, brand /
 * model / part-number identifiers, and various tariff and origin
 * references. Distinct value space from
 * {@see InvoiceReferenceTypeCode} despite some overlap (PON, AAM,
 * TAR, TCO appear in both).
 */
enum LineItemReferenceTypeCode: string
{
    case AFE = 'AFE';
    case AAJ = 'AAJ';
    case ABW = 'ABW';
    case ALX = 'ALX';
    case BRD = 'BRD';
    case DGC = 'DGC';
    case DTC = 'DTC';
    case DTM = 'DTM';
    case DTQ = 'DTQ';
    case DTR = 'DTR';
    case INB = 'INB';
    case ITR = 'ITR';
    case MAK = 'MAK';
    case MID = 'MID';
    case OED = 'OED';
    case OET = 'OET';
    case OID = 'OID';
    case OOR = 'OOR';
    case PAN = 'PAN';
    case PON = 'PON';
    case SE = 'SE';
    case SON = 'SON';
    case SME = 'SME';
    case USM = 'USM';
    case AAM = 'AAM';
    case CFR = 'CFR';
    case DOM = 'DOM';
    case FOR = 'FOR';
    case USG = 'USG';
    case MAT = 'MAT';
    case NLR = 'NLR';
    case DDS = 'DDS';
    case ARN = 'ARN';
    case OP = 'OP';
    case OSC = 'OSC';
    case TAR = 'TAR';
    case TCO = 'TCO';
    case AEI = 'AEI';
    case EXN = 'EXN';
    case AFK = 'AFK';
    case AEA = 'AEA';
    case PTA = 'PTA';
    case CLN = 'CLN';
}

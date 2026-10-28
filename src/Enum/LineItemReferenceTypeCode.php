<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Reference type code allowed at the line-item level.
 *
 * 44 codes drawn from the `invoiceItemReferenceType` sheet of
 * `ExpressReferenceData_2026-10-25.xlsx` — ECCN, DDTC fields, brand / model /
 * part-number identifiers (`SRV` standardized and `MF`
 * non-standardized manufacturer product IDs), `AKO` (Destination Tax
 * Right-To-Use), and various tariff and origin references.
 * Distinct value space from {@see InvoiceReferenceTypeCode} despite
 * some overlap (PON, TAR, TCO appear in both).
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
    case AKO = 'AKO';
    case MF = 'MF';
    case SRV = 'SRV';
}

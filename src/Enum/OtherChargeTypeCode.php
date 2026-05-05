<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Other-charge type code applied to a shipment line.
 *
 * The fifteen values defined in the Reference Data Guide section 13.
 * Each is a five-letter mnemonic for a specific charge category
 * (administration, delivery, expedite, freight cost, fuel surcharge,
 * handling, insurance, etc.) — the descriptive table lives in the
 * reference PDF, not here.
 */
enum OtherChargeTypeCode: string
{
    case ADMIN = 'ADMIN';
    case DELIV = 'DELIV';
    case DOCUM = 'DOCUM';
    case EXPED = 'EXPED';
    case EXCHA = 'EXCHA';
    case FRCST = 'FRCST';
    case SSRGE = 'SSRGE';
    case LOGST = 'LOGST';
    case SOTHR = 'SOTHR';
    case SPKGN = 'SPKGN';
    case PICUP = 'PICUP';
    case HRCRG = 'HRCRG';
    case VATCR = 'VATCR';
    case INSCH = 'INSCH';
    case REVCH = 'REVCH';
}

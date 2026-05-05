<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Reference type code allowed at the invoice level.
 *
 * 19 codes drawn from the `invoiceReferenceType` sheet of
 * `dhl_reference_data.xlsx`. The legacy PDF guide listed many more
 * (41) but the live API restricts this level to the canonical 19;
 * the other codes belong to other reference levels (line-item,
 * shipment, package). Multiple MRN entries are allowed at this
 * level per the workbook note.
 */
enum InvoiceReferenceTypeCode: string
{
    case ACL = 'ACL';
    case AES = 'AES';
    case CID = 'CID';
    case CN = 'CN';
    case CU = 'CU';
    case FTR = 'FTR';
    case INB = 'INB';
    case ITN = 'ITN';
    case MRN = 'MRN';
    case OID = 'OID';
    case PON = 'PON';
    case PTA = 'PTA';
    case PX = 'PX';
    case RMA = 'RMA';
    case SME = 'SME';
    case TAR = 'TAR';
    case TCO = 'TCO';
    case UCN = 'UCN';
    case USM = 'USM';
}

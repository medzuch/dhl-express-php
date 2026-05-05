<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Customs document type code.
 *
 * 55 codes drawn from the `documentTypeCode` sheet of
 * `dhl_reference_data.xlsx`. The same value space applies at the
 * invoice level and the line-item level so a single enum covers
 * both contexts. The wire code `972` cannot start a PHP case name
 * so it gets a descriptive name (`T2LFDispense`) — the wire code
 * remains on `->value`.
 */
enum CustomsDocumentTypeCode: string
{
    case T2LFDispense = '972';
    case AHC = 'AHC';
    case ALC = 'ALC';
    case APP = 'APP';
    case ATA = 'ATA';
    case BEX = 'BEX';
    case CHA = 'CHA';
    case CIT = 'CIT';
    case CIV = 'CIV';
    case CI2 = 'CI2';
    case COO = 'COO';
    case CPA = 'CPA';
    case CRL = 'CRL';
    case CSD = 'CSD';
    case DEX = 'DEX';
    case DGD = 'DGD';
    case DLI = 'DLI';
    case DOV = 'DOV';
    case EDC = 'EDC';
    case ELP = 'ELP';
    case EU1 = 'EU1';
    case EU2 = 'EU2';
    case EUS = 'EUS';
    case EXL = 'EXL';
    case FMA = 'FMA';
    case FSP = 'FSP';
    case HWB = 'HWB';
    case IMP = 'IMP';
    case INV = 'INV';
    case IPA = 'IPA';
    case JLC = 'JLC';
    case LIC = 'LIC';
    case LNP = 'LNP';
    case MFD = 'MFD';
    case NID = 'NID';
    case PAS = 'PAS';
    case PFI = 'PFI';
    case PHY = 'PHY';
    case PLI = 'PLI';
    case POA = 'POA';
    case PCH = 'PCH';
    case PPY = 'PPY';
    case ROD = 'ROD';
    case T2M = 'T2M';
    case TAD = 'TAD';
    case TCS = 'TCS';
    case VET = 'VET';
    case VEX = 'VEX';
    case ORD = 'ORD';
    case OEI = 'OEI';
    case RGR = 'RGR';
    case ICD = 'ICD';
    case BLI = 'BLI';
    case EAD = 'EAD';
    case ETD = 'ETD';
}

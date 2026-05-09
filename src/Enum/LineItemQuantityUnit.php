<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Unit of measurement for a line-item quantity.
 *
 * Matches the `quantity.unitOfMeasurement` enum in
 * `docs/dhl/dhl_openapi.yaml`. Wire codes that start with a digit
 * cannot be PHP case names — they get descriptive PascalCase names
 * while the original wire code remains as the backing `->value`
 * (same convention used by {@see DangerousGoodsContentId}).
 *
 * Digit-starting codes:
 * - `2GM` → {@see self::Centigram}
 * - `3GM` → {@see self::Milligram}
 * - `2M2` → {@see self::SquareFeet}
 * - `3M2` → {@see self::SquareInches}
 * - `4M2` → {@see self::SquareYards}
 */
enum LineItemQuantityUnit: string
{
    case BOX = 'BOX';
    case Centigram = '2GM';
    case M3 = 'M3';
    case DPR = 'DPR';
    case DOZ = 'DOZ';
    case PCS = 'PCS';
    case GM = 'GM';
    case GRS = 'GRS';
    case KG = 'KG';
    case M = 'M';
    case Milligram = '3GM';
    case X = 'X';
    case NO = 'NO';
    case PRS = 'PRS';
    case CM2 = 'CM2';
    case SquareFeet = '2M2';
    case SquareInches = '3M2';
    case M2 = 'M2';
    case SquareYards = '4M2';
    case CM = 'CM';
    case CONE = 'CONE';
    case CT = 'CT';
    case EA = 'EA';
    case LBS = 'LBS';
    case RILL = 'RILL';
    case ROLL = 'ROLL';
    case SET = 'SET';
    case TU = 'TU';
    case KM = 'KM';
    case IN = 'IN';
    case FT = 'FT';
    case YD = 'YD';
    case MI = 'MI';
    case LTR = 'LTR';
    case MMQ = 'MMQ';
    case CM3 = 'CM3';
    case DMQ = 'DMQ';
    case MLT = 'MLT';
    case CLT = 'CLT';
    case DLT = 'DLT';
    case INQ = 'INQ';
    case FT3 = 'FT3';
    case YD3 = 'YD3';
    case GLI = 'GLI';
    case GLL = 'GLL';
    case PT = 'PT';
    case PTI = 'PTI';
    case QTI = 'QTI';
    case PTL = 'PTL';
    case QTL = 'QTL';
    case PTD = 'PTD';
    case OZI = 'OZI';
    case J57 = 'J57';
    case NM3 = 'NM3';
    case SM3 = 'SM3';
    case TNE = 'TNE';
    case LB = 'LB';
    case ONZ = 'ONZ';
    case CEL = 'CEL';
}

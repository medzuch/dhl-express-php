<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Unit of measurement used on customs line items.
 *
 * The 59 codes from the `unitOfMeasurement` sheet of
 * `dhl_reference_data.xlsx`. Distinct from the shipment-level
 * {@see WeightUnit} / {@see DimensionUnit} pairing — this covers
 * the per-line-item quantity unit on commercial invoices (DOZ,
 * PCS, M3, GLI, …) where the customs framework needs a precise
 * unit for declared quantities.
 *
 * Wire codes that start with a digit get descriptive PascalCase
 * case names (PHP does not allow case names to start with a
 * digit); the rest use the wire code directly.
 */
enum UnitOfMeasurement: string
{
    case Centigram = '2GM';
    case SquareFoot = '2M2';
    case Milligram = '3GM';
    case SquareInch = '3M2';
    case SquareYard = '4M2';
    case BOX = 'BOX';
    case CEL = 'CEL';
    case CLT = 'CLT';
    case CM = 'CM';
    case CM2 = 'CM2';
    case CM3 = 'CM3';
    case CONE = 'CONE';
    case CT = 'CT';
    case DLT = 'DLT';
    case DMQ = 'DMQ';
    case DOZ = 'DOZ';
    case DPR = 'DPR';
    case EA = 'EA';
    case FT = 'FT';
    case FT3 = 'FT3';
    case GLI = 'GLI';
    case GLL = 'GLL';
    case GM = 'GM';
    case GRS = 'GRS';
    case IN = 'IN';
    case INQ = 'INQ';
    case J57 = 'J57';
    case KG = 'KG';
    case KM = 'KM';
    case LB = 'LB';
    case LBS = 'LBS';
    case LTR = 'LTR';
    case M = 'M';
    case M2 = 'M2';
    case M3 = 'M3';
    case MI = 'MI';
    case MLT = 'MLT';
    case MMQ = 'MMQ';
    case NM3 = 'NM3';
    case NO = 'NO';
    case ONZ = 'ONZ';
    case OZI = 'OZI';
    case PCS = 'PCS';
    case PRS = 'PRS';
    case PT = 'PT';
    case PTD = 'PTD';
    case PTI = 'PTI';
    case PTL = 'PTL';
    case QTI = 'QTI';
    case QTL = 'QTL';
    case RILL = 'RILL';
    case ROLL = 'ROLL';
    case SET = 'SET';
    case SM3 = 'SM3';
    case TNE = 'TNE';
    case TU = 'TU';
    case X = 'X';
    case YD = 'YD';
    case YD3 = 'YD3';
}

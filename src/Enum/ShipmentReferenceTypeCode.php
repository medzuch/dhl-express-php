<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express shipment-level reference type codes.
 *
 * Sourced from the `customerShipmentReferenceType` sheet of
 * `ExpressReferenceData_2026-10-25.xlsx` (16 unique codes). The backing value is
 * the exact wire code. The 47 codes the 2026-10-25 workbook retired
 * (e.g. `AAO`, `BOL`, `HWB`, `MR1`–`MR6`) are rejected by the live
 * API with error 7021 and are no longer modelled; responses may still
 * carry them for older shipments, so hydrators use `tryFrom()`.
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
    case MRN = 'MRN';
    case OID = 'OID';
    case PON = 'PON';
    case RMA = 'RMA';
    case UCN = 'UCN';
    case WLK = 'WLK';
    case AAJ = 'AAJ';
    case ACL = 'ACL';
    case AFE = 'AFE';
    case CID = 'CID';
    case CN = 'CN';
    case CU = 'CU';
    case DGC = 'DGC';
    case ITN = 'ITN';
    case AFM = 'AFM';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Reference type code attached to an individual package on a
 * shipment.
 *
 * 40 codes drawn from the `customerPackageReferenceType` sheet of
 * `ExpressReferenceData_2026-10-25.xlsx`. The 41 codes the 2026-10-25 workbook
 * retired (e.g. `AAO`, `CDN`, `HWB`) are no longer modelled. The xlsx repeats most codes once per
 * `applicableCountryCode` — the enum carries the unique typeCode
 * set; per-country applicability is a builder/runtime concern.
 *
 * Distinct from {@see InvoiceReferenceTypeCode} (invoice-level) and
 * {@see LineItemReferenceTypeCode} (line-item-level) — the value
 * space is its own. CU (consignor reference) is the DHL default.
 */
enum PackageReferenceTypeCode: string
{
    /**
     * AAJ — Delivery Order number. Listed by the workbook and the
     * 3.4.0 reference data guide but not by the 3.4.0 OpenAPI spec;
     * kept under the two-of-three-sources rule.
     */
    case AAJ = 'AAJ';
    case ABW = 'ABW';
    case ACL = 'ACL';
    case AES = 'AES';
    case AFE = 'AFE';
    case ALX = 'ALX';
    case BRD = 'BRD';
    case CFR = 'CFR';
    case CID = 'CID';
    case CN = 'CN';
    case CU = 'CU';
    case DGC = 'DGC';
    case DOM = 'DOM';
    case DTC = 'DTC';
    case DTM = 'DTM';
    case DTQ = 'DTQ';
    case DTR = 'DTR';
    case FOR = 'FOR';
    case FTR = 'FTR';
    case INB = 'INB';
    case ITN = 'ITN';
    case ITR = 'ITR';
    case MAK = 'MAK';
    case MAT = 'MAT';
    case MRN = 'MRN';
    case NLR = 'NLR';
    case OED = 'OED';
    case OET = 'OET';
    case OID = 'OID';
    case OOR = 'OOR';
    case PAN = 'PAN';
    case PON = 'PON';
    case RMA = 'RMA';
    case SE = 'SE';
    case SME = 'SME';
    case SON = 'SON';
    case UCN = 'UCN';
    case USG = 'USG';
    case USM = 'USM';
    case WLK = 'WLK';
}

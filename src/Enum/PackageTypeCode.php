<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express Global Package Type Code.
 *
 * The 18 DHL-supplied packaging types: the 17 listed in the
 * `packageTypeCode` sheet of `ExpressReferenceData_2026-10-25.xlsx`
 * plus `CE1` (see its case docblock). The wine boxes `WB1`/`WB2`/
 * `WB3`/`WB6` were retired by DHL and are no longer modelled. Backing
 * values are the wire codes; the case names are descriptive where
 * the wire code is digit-prefixed (PHP does not allow case names to
 * start with a digit) and identical to the wire code where it makes
 * a valid identifier on its own.
 *
 * Each box variant has a fixed maximum dimension and weight defined
 * by DHL — those tables live in the reference workbook, not here.
 */
enum PackageTypeCode: string
{
    case TBS = 'TBS';
    case TBL = 'TBL';
    case CardEnvelopeMetric = '1CE';
    /**
     * CE1 — Card Envelope (imperial). Listed by the 3.4.0 OpenAPI spec
     * and the 3.4.0 reference data guide but not by the 2026-10-25
     * workbook; kept under the two-of-three-sources rule.
     */
    case CardEnvelopeImperial = 'CE1';
    case Box2Cube = '2BC';
    case XPD = 'XPD';
    case Box2Pizza = '2BP';
    case BB1 = 'BB1';
    case BB2 = 'BB2';
    case BB3 = 'BB3';
    case BB6 = 'BB6';
    case Box2Shoe = '2BX';
    case Box3 = '3BX';
    case Box4 = '4BX';
    case Box5JumboSmall = '5BX';
    case Box6 = '6BX';
    case Box7 = '7BX';
    case Box8JumboLarge = '8BX';
}

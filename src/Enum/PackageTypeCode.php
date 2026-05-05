<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express Global Package Type Code.
 *
 * The 22 DHL-supplied packaging types listed in the
 * `packageTypeCode` sheet of `dhl_reference_data.xlsx`. Backing
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
    case CardEnvelopeImperial = 'CE1';
    case Box2Cube = '2BC';
    case XPD = 'XPD';
    case Box2Pizza = '2BP';
    case WB1 = 'WB1';
    case WB2 = 'WB2';
    case WB3 = 'WB3';
    case WB6 = 'WB6';
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

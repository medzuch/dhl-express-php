<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Event type code returned in tracking responses.
 *
 * The 65 two-letter codes from the `trackingEventCode` sheet of
 * `dhl_reference_data.xlsx`. Each tracked shipment progression
 * step (`OK` delivered, `PU` shipment pick up, `CR` clearance
 * release, `RT` returned to consignor, …) carries one of these.
 *
 * The `visibleToCustomer` flag on each row in the workbook denotes
 * whether DHL surfaces the event to end customers or keeps it
 * operational-only — that flag is not encoded here because callers
 * care about the code itself, not its visibility classification.
 */
enum TrackingEventCode: string
{
    case AD = 'AD';
    case AF = 'AF';
    case AR = 'AR';
    case BA = 'BA';
    case BL = 'BL';
    case BN = 'BN';
    case BR = 'BR';
    case CA = 'CA';
    case CC = 'CC';
    case CD = 'CD';
    case CI = 'CI';
    case CM = 'CM';
    case CR = 'CR';
    case CS = 'CS';
    case CU = 'CU';
    case DD = 'DD';
    case DF = 'DF';
    case DG = 'DG';
    case DI = 'DI';
    case DM = 'DM';
    case DP = 'DP';
    case DS = 'DS';
    case EM = 'EM';
    case ES = 'ES';
    case FD = 'FD';
    case HI = 'HI';
    case HN = 'HN';
    case HO = 'HO';
    case HP = 'HP';
    case IA = 'IA';
    case IC = 'IC';
    case LV = 'LV';
    case MC = 'MC';
    case MD = 'MD';
    case MF = 'MF';
    case MS = 'MS';
    case NA = 'NA';
    case ND = 'ND';
    case NH = 'NH';
    case OH = 'OH';
    case OK = 'OK';
    case PD = 'PD';
    case PL = 'PL';
    case PU = 'PU';
    case PW = 'PW';
    case PY = 'PY';
    case RD = 'RD';
    case RR = 'RR';
    case RT = 'RT';
    case RW = 'RW';
    case SA = 'SA';
    case SC = 'SC';
    case SD = 'SD';
    case SI = 'SI';
    case SM = 'SM';
    case SS = 'SS';
    case ST = 'ST';
    case TD = 'TD';
    case TI = 'TI';
    case TP = 'TP';
    case TR = 'TR';
    case TT = 'TT';
    case UD = 'UD';
    case UV = 'UV';
    case WC = 'WC';
}

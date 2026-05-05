<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Three-letter language code accepted by the DHL Express API.
 *
 * 46 codes from the `languageCode` sheet of
 * `dhl_reference_data.xlsx`. Used for the `Accept-Language`
 * request header (default `eng`) and for per-document language
 * selection on commercial invoices and shipment paperwork.
 *
 * Wire values are lowercase 3-letter codes (mostly ISO 639-2/B
 * style); case names are the same code in PascalCase. Note the
 * Chinese duo: `Chi` is Simplified and `Zho` is Traditional, per
 * the workbook.
 */
enum LanguageCode: string
{
    case Alb = 'alb';
    case Ara = 'ara';
    case Bak = 'bak';
    case Bos = 'bos';
    case Bul = 'bul';
    case Chi = 'chi';
    case Cze = 'cze';
    case Dan = 'dan';
    case Dut = 'dut';
    case Eng = 'eng';
    case Est = 'est';
    case Ewe = 'ewe';
    case Fin = 'fin';
    case Fre = 'fre';
    case Geo = 'geo';
    case Ger = 'ger';
    case Gre = 'gre';
    case Heb = 'heb';
    case Hrv = 'hrv';
    case Hun = 'hun';
    case Ice = 'ice';
    case Ind = 'ind';
    case Ita = 'ita';
    case Jpn = 'jpn';
    case Kor = 'kor';
    case Lav = 'lav';
    case Lit = 'lit';
    case Mac = 'mac';
    case Nno = 'nno';
    case Nor = 'nor';
    case Pol = 'pol';
    case Por = 'por';
    case Rum = 'rum';
    case Rus = 'rus';
    case Slo = 'slo';
    case Slv = 'slv';
    case Spa = 'spa';
    case Srd = 'srd';
    case Srp = 'srp';
    case Swe = 'swe';
    case Tha = 'tha';
    case Tur = 'tur';
    case Twi = 'twi';
    case Ukr = 'ukr';
    case Vie = 'vie';
    case Zho = 'zho';
}

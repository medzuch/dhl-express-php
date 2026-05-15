<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Output format for shipment labels and waybill documents.
 *
 * The four values DHL accepts under shipment `outputImageProperties.encodingFormat`:
 * Adobe PDF for office printers, Zebra ZPL and EPL for thermal label
 * printers, and LP2 for direct thermal output. Invoice and shipment
 * receipt always come back as PDF regardless of this setting.
 *
 * Distinct from the get-image download format (pdf or tiff) which
 * is a different field entirely — see {@see GetImageEncodingFormat}.
 */
enum LabelEncodingFormat: string
{
    case Pdf = 'pdf';
    case Zpl = 'zpl';
    case Lp2 = 'lp2';
    case Epl = 'epl';
}

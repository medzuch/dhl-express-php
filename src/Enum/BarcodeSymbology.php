<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Symbology of a customer-supplied barcode printed on a DHL transport
 * label.
 *
 * Mirrors `outputImageProperties.customerBarcodes[].symbologyCode`
 * (spec line 10861).
 */
enum BarcodeSymbology: string
{
    case Code93 = '93';
    case Code39 = '39';
    case Code128 = '128';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Position of a bespoke barcode on the transport label.
 *
 * Mirrors `package.labelBarcodes[].position` (spec line 14751).
 */
enum LabelBarcodePosition: string
{
    case Left = 'left';
    case Right = 'right';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\BarcodeSymbology;
use Medzuch\DhlExpress\Enum\LabelBarcodePosition;

/**
 * One entry in `package.labelBarcodes` (max 2).
 *
 * Requires the ECOM26_84CI_003 transport-label template on the
 * matching `imageOptions[].typeCode=label` entry. All four fields are
 * required per spec.
 */
final readonly class LabelBarcode
{
    public function __construct(
        public LabelBarcodePosition $position,
        public BarcodeSymbology $symbologyCode,
        public string $content,
        public string $textBelowBarcode,
    ) {
    }

    /**
     * @return array{position: string, symbologyCode: string, content: string, textBelowBarcode: string}
     */
    public function toArray(): array
    {
        return [
            'position' => $this->position->value,
            'symbologyCode' => $this->symbologyCode->value,
            'content' => $this->content,
            'textBelowBarcode' => $this->textBelowBarcode,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\BarcodeSymbology;

/**
 * One entry in `outputImageProperties.customerBarcodes` (max 1 item).
 *
 * Requires the ECOM26_84CI_002 or ECOM26_84CI_003 transport-label
 * template to be selected on the matching `imageOptions[]` item.
 */
final readonly class CustomerBarcode
{
    public function __construct(
        public string $content,
        public BarcodeSymbology $symbologyCode,
        public ?string $textBelowBarcode = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = [
            'content' => $this->content,
            'symbologyCode' => $this->symbologyCode->value,
        ];

        if ($this->textBelowBarcode !== null) {
            $payload['textBelowBarcode'] = $this->textBelowBarcode;
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Single image option inside an invoice-upload
 * {@see InvoiceOutputImageProperties} block.
 *
 * Distinct from {@see ImageOption} (used on create-shipment): the
 * invoice variant is fixed to `typeCode=invoice` and exposes only the
 * three fields the upload-invoice schema allows (`typeCode`,
 * `templateName`, `isRequested`). The full label/QR/waybill option
 * vocabulary on `OutputImageProperties` does not apply here.
 */
final readonly class InvoiceImageOption
{
    public function __construct(
        public ?string $templateName = null,
        public ?bool $isRequested = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = ['typeCode' => 'invoice'];

        if ($this->templateName !== null) {
            $payload['templateName'] = $this->templateName;
        }
        if ($this->isRequested !== null) {
            $payload['isRequested'] = $this->isRequested;
        }

        return $payload;
    }
}

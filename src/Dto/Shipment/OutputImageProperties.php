<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LabelEncodingFormat;

/**
 * Output formatting controls for shipment labels and documents.
 *
 * Mirrors `supermodelIoLogisticsExpressOutputImageProperties`. The
 * five `split…` / `…InOneImage` toggles are mutually shaping flags
 * documented in the spec — only one usually makes sense for a given
 * caller; the wire contract doesn't enforce exclusivity, so we don't
 * either.
 *
 * Note: `renderDHLLogo` and `fitLabelsToA4` are per-document toggles
 * and live on {@see ImageOption}, not here (spec lines 11021–11037).
 */
final readonly class OutputImageProperties
{
    /**
     * @param list<ImageOption>     $imageOptions
     * @param list<CustomerBarcode> $customerBarcodes  max 1 entry per spec
     * @param list<CustomerLogo>    $customerLogos     max 1 entry per spec
     */
    public function __construct(
        public ?LabelEncodingFormat $encodingFormat = null,
        public array $imageOptions = [],
        public ?int $printerDPI = null,
        public array $customerBarcodes = [],
        public array $customerLogos = [],
        public ?bool $splitTransportAndWaybillDocLabels = null,
        public ?bool $allDocumentsInOneImage = null,
        public ?bool $splitDocumentsByPages = null,
        public ?bool $splitInvoiceAndReceipt = null,
        public ?bool $receiptAndLabelsInOneImage = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->encodingFormat !== null) {
            $payload['encodingFormat'] = $this->encodingFormat->value;
        }
        if ($this->imageOptions !== []) {
            $payload['imageOptions'] = array_map(
                static fn (ImageOption $option): array => $option->toArray(),
                $this->imageOptions,
            );
        }
        if ($this->printerDPI !== null) {
            $payload['printerDPI'] = $this->printerDPI;
        }
        if ($this->customerBarcodes !== []) {
            $payload['customerBarcodes'] = array_map(
                static fn (CustomerBarcode $barcode): array => $barcode->toArray(),
                $this->customerBarcodes,
            );
        }
        if ($this->customerLogos !== []) {
            $payload['customerLogos'] = array_map(
                static fn (CustomerLogo $logo): array => $logo->toArray(),
                $this->customerLogos,
            );
        }
        if ($this->splitTransportAndWaybillDocLabels !== null) {
            $payload['splitTransportAndWaybillDocLabels'] = $this->splitTransportAndWaybillDocLabels;
        }
        if ($this->allDocumentsInOneImage !== null) {
            $payload['allDocumentsInOneImage'] = $this->allDocumentsInOneImage;
        }
        if ($this->splitDocumentsByPages !== null) {
            $payload['splitDocumentsByPages'] = $this->splitDocumentsByPages;
        }
        if ($this->splitInvoiceAndReceipt !== null) {
            $payload['splitInvoiceAndReceipt'] = $this->splitInvoiceAndReceipt;
        }
        if ($this->receiptAndLabelsInOneImage !== null) {
            $payload['receiptAndLabelsInOneImage'] = $this->receiptAndLabelsInOneImage;
        }

        return $payload;
    }
}

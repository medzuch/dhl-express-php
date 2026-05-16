<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LabelEncodingFormat;

/**
 * Output formatting controls for add-piece labels and documents.
 *
 * Mirrors the output image properties sub-object in the add-piece
 * (`PATCH /shipments/{id}/add-piece`) request body. Structurally
 * similar to {@see OutputImageProperties} for `POST /shipments` but
 * kept separate because the two endpoints have slightly different
 * field sets.
 */
final readonly class AddPieceOutputImageProperties
{
    /**
     * @param list<ImageOption>     $imageOptions
     * @param list<CustomerBarcode> $customerBarcodes max 1 entry per spec
     * @param list<CustomerLogo>    $customerLogos    max 1 entry per spec
     */
    public function __construct(
        public ?LabelEncodingFormat $encodingFormat = null,
        public ?int $printerDPI = null,
        public array $imageOptions = [],
        public ?bool $returnAllPieceLabels = null,
        public ?bool $splitTransportAndWaybillDocLabels = null,
        public ?bool $allDocumentsInOneImage = null,
        public ?bool $splitDocumentsByPages = null,
        public array $customerBarcodes = [],
        public array $customerLogos = [],
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
        if ($this->printerDPI !== null) {
            $payload['printerDPI'] = $this->printerDPI;
        }
        if ($this->imageOptions !== []) {
            $payload['imageOptions'] = array_map(
                static fn (ImageOption $option): array => $option->toArray(),
                $this->imageOptions,
            );
        }
        if ($this->returnAllPieceLabels !== null) {
            $payload['returnAllPieceLabels'] = $this->returnAllPieceLabels;
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

        return $payload;
    }
}

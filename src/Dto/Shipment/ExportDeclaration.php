<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\ExportReasonType;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\ShipmentPurpose;

/**
 * Export declaration on a shipment content block or an invoice-data
 * upload.
 *
 * `lineItems` (1–1000 items) and `invoice` are required. MyDHL API
 * 3.4.0 also requires an `incoterm` on every declaration on the wire;
 * it is nullable here only so
 * {@see \Medzuch\DhlExpress\Builder\CreateShipmentBuilder::build()}
 * can fill it from the shipment-level incoterm. The invoice-data
 * upload requests reject declarations without one.
 *
 * Optional 3.4.0 fields: `exportReason` (1–30 chars),
 * `declarationNotes` (≤ 3), `packageMarks`, `payerVATNumber`. Like the
 * invoice signature/summary fields and line-item
 * `additionalInformation`, they exist only in the Create Shipment
 * schema. {@see self::toArray()} is the Create Shipment shape;
 * {@see self::toInvoiceDataArray()} is the invoice-data upload shape,
 * and {@see self::invoiceDataUploadErrors()} lists what keeps a
 * declaration from being valid for an upload.
 * Added to a shipment via
 * {@see \Medzuch\DhlExpress\Builder\CreateShipmentBuilder::addExportDeclaration()}.
 */
final readonly class ExportDeclaration
{
    /**
     * @param list<ExportLineItem>          $lineItems
     * @param list<ExportRemark>            $remarks
     * @param list<AdditionalCharge>        $additionalCharges
     * @param list<LineItemCustomsDocument> $customsDocuments
     * @param list<DeclarationNote>         $declarationNotes
     */
    public function __construct(
        public array $lineItems,
        public ExportInvoice $invoice,
        public array $remarks = [],
        public array $additionalCharges = [],
        public ?string $placeOfIncoterm = null,
        public ?string $recipientReference = null,
        public ?Exporter $exporter = null,
        public ?ExportReasonType $exportReasonType = null,
        public ?ShipmentPurpose $shipmentType = null,
        public array $customsDocuments = [],
        public ?Incoterm $incoterm = null,
        public ?string $exportReason = null,
        public array $declarationNotes = [],
        public ?string $packageMarks = null,
        public ?string $payerVATNumber = null,
    ) {
        if ($exportReason !== null && (strlen($exportReason) < 1 || strlen($exportReason) > 30)) {
            throw new \InvalidArgumentException('ExportDeclaration exportReason must be 1–30 characters.');
        }
        if (count($declarationNotes) > 3) {
            throw new \InvalidArgumentException('ExportDeclaration declarationNotes accepts at most 3 entries.');
        }
    }

    /**
     * Copy of this declaration with the given incoterm; every other
     * field is unchanged. Used by the Create Shipment builder to apply
     * the shipment-level incoterm as a default.
     */
    public function withIncoterm(Incoterm $incoterm): self
    {
        return new self(
            lineItems: $this->lineItems,
            invoice: $this->invoice,
            remarks: $this->remarks,
            additionalCharges: $this->additionalCharges,
            placeOfIncoterm: $this->placeOfIncoterm,
            recipientReference: $this->recipientReference,
            exporter: $this->exporter,
            exportReasonType: $this->exportReasonType,
            shipmentType: $this->shipmentType,
            customsDocuments: $this->customsDocuments,
            incoterm: $incoterm,
            exportReason: $this->exportReason,
            declarationNotes: $this->declarationNotes,
            packageMarks: $this->packageMarks,
            payerVATNumber: $this->payerVATNumber,
        );
    }

    /**
     * Field paths (relative to this declaration) that make it invalid for
     * the invoice-data upload schema: a missing `incoterm` or
     * `invoice.function`, or any set Create-Shipment-only field.
     *
     * @return list<string>
     */
    public function invoiceDataUploadErrors(): array
    {
        $errors = [];

        if ($this->incoterm === null) {
            $errors[] = 'incoterm';
        }
        if ($this->invoice->function === null) {
            $errors[] = 'invoice.function';
        }
        foreach ($this->invoice->shipmentOnlyFields() as $field) {
            $errors[] = 'invoice.' . $field;
        }

        $shipmentOnly = [
            'exportReason' => $this->exportReason !== null,
            'declarationNotes' => $this->declarationNotes !== [],
            'packageMarks' => $this->packageMarks !== null,
            'payerVATNumber' => $this->payerVATNumber !== null,
        ];
        foreach (array_keys(array_filter($shipmentOnly)) as $field) {
            $errors[] = $field;
        }

        foreach ($this->lineItems as $index => $item) {
            if ($item->additionalInformation !== []) {
                $errors[] = "lineItems[{$index}].additionalInformation";
            }
        }

        return $errors;
    }

    /**
     * Create Shipment shape (`content.exportDeclarations[]`).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload($this->invoice->toArray());
    }

    /**
     * Invoice-data upload shape (`content.exportDeclaration[]`). Callers
     * must check {@see self::invoiceDataUploadErrors()} first; the upload
     * request DTOs do this in their constructors.
     *
     * @return array<string, mixed>
     */
    public function toInvoiceDataArray(): array
    {
        return $this->payload($this->invoice->toInvoiceDataArray());
    }

    /**
     * @param array<string, mixed> $invoice
     *
     * @return array<string, mixed>
     */
    private function payload(array $invoice): array
    {
        $payload = [
            'lineItems' => array_map(
                static fn (ExportLineItem $item): array => $item->toArray(),
                $this->lineItems,
            ),
            'invoice' => $invoice,
        ];

        if ($this->remarks !== []) {
            $payload['remarks'] = array_map(
                static fn (ExportRemark $remark): array => $remark->toArray(),
                $this->remarks,
            );
        }
        if ($this->additionalCharges !== []) {
            $payload['additionalCharges'] = array_map(
                static fn (AdditionalCharge $charge): array => $charge->toArray(),
                $this->additionalCharges,
            );
        }
        if ($this->placeOfIncoterm !== null) {
            $payload['placeOfIncoterm'] = $this->placeOfIncoterm;
        }
        if ($this->recipientReference !== null) {
            $payload['recipientReference'] = $this->recipientReference;
        }
        if ($this->exporter !== null) {
            $payload['exporter'] = $this->exporter->toArray();
        }
        if ($this->exportReasonType !== null) {
            $payload['exportReasonType'] = $this->exportReasonType->value;
        }
        if ($this->shipmentType !== null) {
            $payload['shipmentType'] = $this->shipmentType->value;
        }
        if ($this->customsDocuments !== []) {
            $payload['customsDocuments'] = array_map(
                static fn (LineItemCustomsDocument $doc): array => $doc->toArray(),
                $this->customsDocuments,
            );
        }
        if ($this->incoterm !== null) {
            $payload['incoterm'] = $this->incoterm->value;
        }
        if ($this->exportReason !== null) {
            $payload['exportReason'] = $this->exportReason;
        }
        if ($this->declarationNotes !== []) {
            $payload['declarationNotes'] = array_map(
                static fn (DeclarationNote $note): array => $note->toArray(),
                $this->declarationNotes,
            );
        }
        if ($this->packageMarks !== null) {
            $payload['packageMarks'] = $this->packageMarks;
        }
        if ($this->payerVATNumber !== null) {
            $payload['payerVATNumber'] = $this->payerVATNumber;
        }

        return $payload;
    }
}

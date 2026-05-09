<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\ExportReasonType;
use Medzuch\DhlExpress\Enum\ShipmentPurpose;

/**
 * Top-level export declaration on a shipment content block.
 *
 * `lineItems` is required (1–1000 items). All other fields are
 * optional. Constructed via
 * {@see \Medzuch\DhlExpress\Builder\CreateShipmentBuilder::withExportDeclaration()}
 * as part of a customs-declarable shipment.
 */
final readonly class ExportDeclaration
{
    /**
     * @param list<ExportLineItem>          $lineItems
     * @param list<ExportRemark>            $remarks
     * @param list<AdditionalCharge>        $additionalCharges
     * @param list<LineItemCustomsDocument> $customsDocuments
     */
    public function __construct(
        public array $lineItems,
        public ?ExportInvoice $invoice = null,
        public array $remarks = [],
        public array $additionalCharges = [],
        public ?string $placeOfIncoterm = null,
        public ?string $recipientReference = null,
        public ?Exporter $exporter = null,
        public ?ExportReasonType $exportReasonType = null,
        public ?ShipmentPurpose $shipmentType = null,
        public array $customsDocuments = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'lineItems' => array_map(
                static fn (ExportLineItem $item): array => $item->toArray(),
                $this->lineItems,
            ),
        ];

        if ($this->invoice !== null) {
            $payload['invoice'] = $this->invoice->toArray();
        }
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

        return $payload;
    }
}

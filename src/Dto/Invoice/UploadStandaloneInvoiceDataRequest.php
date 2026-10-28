<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Invoice;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceCustomerDetails;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceOutputImageProperties;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;

/**
 * Request body for `POST /invoices/upload-invoice-data`.
 *
 * The standalone invoice-upload variant — used when invoice data is
 * uploaded before (or independently of) the shipment that carries it.
 * Mirrors `supermodelIoLogisticsExpressUploadInvoiceDataRequestSID`.
 *
 * Schema-wise this is the PATCH /shipments/{id}/upload-invoice-data
 * payload plus a `shipmentTrackingNumber` carried inside the body
 * instead of the URL path. Empty `shipmentTrackingNumber` is allowed
 * for the "upload now, attach a shipment later via shipper reference"
 * flow; in that case the first `accounts[]` entry must carry
 * `typeCode=shipper` per the spec.
 */
final readonly class UploadStandaloneInvoiceDataRequest
{
    /**
     * @param list<ExportDeclaration> $exportDeclarations
     * @param list<Account>           $accounts
     */
    public function __construct(
        public array $exportDeclarations,
        public string $currency,
        public UnitSystem $unitOfMeasurement,
        public ?TrackingNumber $shipmentTrackingNumber = null,
        public array $accounts = [],
        public ?DateTimeImmutable $plannedShipDate = null,
        public ?InvoiceOutputImageProperties $outputImageProperties = null,
        public ?InvoiceCustomerDetails $customerDetails = null,
    ) {
        // The upload schema requires incoterm and invoice.function on every
        // declaration (there is no shipment-level incoterm to inherit) and
        // rejects the Create-Shipment-only fields; never drop them silently.
        foreach ($exportDeclarations as $index => $declaration) {
            $errors = $declaration->invoiceDataUploadErrors();
            if ($errors !== []) {
                throw new \InvalidArgumentException(sprintf(
                    'UploadStandaloneInvoiceDataRequest exportDeclarations[%d] is not valid for an invoice-data upload: %s.',
                    $index,
                    implode(', ', array_map(
                        static fn (string $field): string => "exportDeclarations[{$index}].{$field}",
                        $errors,
                    )),
                ));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->shipmentTrackingNumber !== null) {
            $payload['shipmentTrackingNumber'] = $this->shipmentTrackingNumber->value;
        }

        if ($this->plannedShipDate !== null) {
            $payload['plannedShipDate'] = $this->plannedShipDate->format('Y-m-d');
        }

        if ($this->accounts !== []) {
            $payload['accounts'] = array_map(
                static fn (Account $account): array => $account->toArray(),
                $this->accounts,
            );
        }

        $payload['content'] = [
            'exportDeclaration' => array_map(
                static fn (ExportDeclaration $declaration): array => $declaration->toInvoiceDataArray(),
                $this->exportDeclarations,
            ),
            'currency' => $this->currency,
            'unitOfMeasurement' => $this->unitOfMeasurement->value,
        ];

        if ($this->outputImageProperties !== null) {
            $outputImage = $this->outputImageProperties->toArray();
            if ($outputImage !== []) {
                $payload['outputImageProperties'] = $outputImage;
            }
        }

        if ($this->customerDetails !== null) {
            $customerDetails = $this->customerDetails->toArray();
            if ($customerDetails !== []) {
                $payload['customerDetails'] = $customerDetails;
            }
        }

        return $payload;
    }
}

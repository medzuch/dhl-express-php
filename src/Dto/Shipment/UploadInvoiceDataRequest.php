<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Enum\UnitSystem;

/**
 * Request body for `PATCH /shipments/{id}/upload-invoice-data`.
 *
 * Uploads structured invoice/customs data (as opposed to image files)
 * for Paperless Trade (PLT). The export declarations are nested under
 * `content.exportDeclaration` in the wire format.
 *
 * Optional {@see $outputImageProperties} lets DHL render and return an
 * invoice PDF/image alongside the data upload (avoids a separate
 * `get-image` call). Optional {@see $customerDetails} lets callers
 * override the parties already on the shipment with invoice-specific
 * seller/buyer/importer/exporter information.
 */
final readonly class UploadInvoiceDataRequest
{
    /**
     * @param list<ExportDeclaration> $exportDeclarations
     * @param list<Account>           $accounts
     */
    public function __construct(
        public array $exportDeclarations,
        public string $currency,
        public UnitSystem $unitOfMeasurement,
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
                    'UploadInvoiceDataRequest exportDeclarations[%d] is not valid for an invoice-data upload: %s.',
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

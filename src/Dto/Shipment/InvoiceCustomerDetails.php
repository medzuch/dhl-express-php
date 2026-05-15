<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Optional `customerDetails` override on invoice-upload requests.
 *
 * Mirrors the `customerDetails` block of
 * `supermodelIoLogisticsExpressUploadInvoiceDataRequest` (and the SID
 * variant). The shipment-level {@see CustomerDetails} requires shipper
 * and receiver; the invoice-upload variant exposes a different set
 * of roles, all optional, used to override or supplement data already
 * carried on the underlying shipment.
 */
final readonly class InvoiceCustomerDetails
{
    public function __construct(
        public ?InvoiceParty $sellerDetails = null,
        public ?InvoiceParty $buyerDetails = null,
        public ?InvoiceParty $importerDetails = null,
        public ?InvoiceParty $exporterDetails = null,
        public ?InvoiceParty $manufacturerDetails = null,
        public ?InvoiceParty $ultimateConsigneeDetails = null,
        public ?InvoiceParty $brokerDetails = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->sellerDetails !== null) {
            $payload['sellerDetails'] = $this->sellerDetails->toArray();
        }
        if ($this->buyerDetails !== null) {
            $payload['buyerDetails'] = $this->buyerDetails->toArray();
        }
        if ($this->importerDetails !== null) {
            $payload['importerDetails'] = $this->importerDetails->toArray();
        }
        if ($this->exporterDetails !== null) {
            $payload['exporterDetails'] = $this->exporterDetails->toArray();
        }
        if ($this->manufacturerDetails !== null) {
            $payload['manufacturerDetails'] = $this->manufacturerDetails->toArray();
        }
        if ($this->ultimateConsigneeDetails !== null) {
            $payload['ultimateConsigneeDetails'] = $this->ultimateConsigneeDetails->toArray();
        }
        if ($this->brokerDetails !== null) {
            $payload['brokerDetails'] = $this->brokerDetails->toArray();
        }

        return $payload;
    }
}

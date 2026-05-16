<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\InvoicePartyTypeCode;

/**
 * One optional customer party on a create-shipment request.
 *
 * Covers the eight non-required `customerDetails` slots:
 * `buyerDetails`, `importerDetails`, `exporterDetails`,
 * `sellerDetails`, `payerDetails`, `manufacturerDetails`,
 * `ultimateConsigneeDetails`, `brokerDetails`. All share the same
 * shape (spec lines 11201–11438): a {@see ContactAddress} (which
 * carries postalAddress + contactInformation) plus optional
 * `registrationNumbers`, `bankDetails`, and `typeCode`.
 *
 * `typeCode` reuses {@see InvoicePartyTypeCode} — the create-shipment
 * spec uses the same lowercase wire vocabulary
 * (`business`/`direct_consumer`/…) as the invoice-upload endpoint,
 * not the two-letter {@see \Medzuch\DhlExpress\Enum\BusinessPartyTypeCode}.
 *
 * Required parties `shipperDetails`/`receiverDetails` continue to use
 * the plainer {@see ContactAddress} directly on
 * {@see CustomerDetails} — they cannot carry registration numbers or
 * bank details and don't need this wrapper.
 */
final readonly class ShipmentParty
{
    /**
     * @param list<RegistrationNumber> $registrationNumbers
     */
    public function __construct(
        public ContactAddress $contact,
        public array $registrationNumbers = [],
        public ?BankDetails $bankDetails = null,
        public ?InvoicePartyTypeCode $typeCode = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = $this->contact->toArray();

        if ($this->registrationNumbers !== []) {
            $payload['registrationNumbers'] = array_map(
                static fn (RegistrationNumber $r): array => $r->toArray(),
                $this->registrationNumbers,
            );
        }
        if ($this->bankDetails !== null) {
            $payload['bankDetails'] = [$this->bankDetails->toArray()];
        }
        if ($this->typeCode !== null) {
            $payload['typeCode'] = $this->typeCode->value;
        }

        return $payload;
    }
}

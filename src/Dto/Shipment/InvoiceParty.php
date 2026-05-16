<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\InvoicePartyTypeCode;

/**
 * One party slot inside an invoice-upload `customerDetails` block
 * (seller, buyer, importer, exporter, manufacturer, ultimateConsignee,
 * or broker).
 *
 * Each role shares the same shape: required postal address + contact
 * information, plus optional business-party `typeCode` and up to ten
 * `registrationNumbers`. Modelled on top of {@see ContactAddress}
 * rather than re-implementing postal/contact fields.
 */
final readonly class InvoiceParty
{
    /**
     * @param list<RegistrationNumber> $registrationNumbers
     */
    public function __construct(
        public ContactAddress $contactAddress,
        public ?InvoicePartyTypeCode $typeCode = null,
        public array $registrationNumbers = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = $this->contactAddress->toArray();

        if ($this->typeCode !== null) {
            $payload['typeCode'] = $this->typeCode->value;
        }

        if ($this->registrationNumbers !== []) {
            $payload['registrationNumbers'] = array_map(
                static fn (RegistrationNumber $rn): array => $rn->toArray(),
                $this->registrationNumbers,
            );
        }

        return $payload;
    }
}

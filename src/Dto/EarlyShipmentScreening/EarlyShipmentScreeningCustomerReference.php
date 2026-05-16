<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\EarlyShipmentScreening;

/**
 * One `customerReferences[]` entry on an early-shipment-screening
 * request.
 *
 * The spec only permits the `CU` typeCode for this endpoint
 * ("reference number of consignor"). Default carries that value;
 * callers rarely need to override it. At least one `customerReferences`
 * entry OR a `shipmentId` identifier must be present on the request.
 */
final readonly class EarlyShipmentScreeningCustomerReference
{
    public function __construct(
        public string $value,
        public string $typeCode = 'CU',
    ) {
    }

    /**
     * @return array{value: string, typeCode: string}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'typeCode' => $this->typeCode,
        ];
    }
}

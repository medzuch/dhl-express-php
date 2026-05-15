<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\EarlyShipmentScreening;

use Medzuch\DhlExpress\ValueObject\TrackingNumber;

/**
 * One `identifiers[]` entry on an early-shipment-screening request.
 *
 * The spec only permits the `shipmentId` typeCode for this endpoint
 * (a DHL waybill number). At least one `customerReferences` entry OR
 * a `shipmentId` identifier must be present on the request.
 */
final readonly class EarlyShipmentScreeningIdentifier
{
    public function __construct(
        public TrackingNumber $value,
        public string $typeCode = 'shipmentId',
    ) {
    }

    /**
     * @return array{typeCode: string, value: string}
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode,
            'value' => $this->value->value,
        ];
    }
}

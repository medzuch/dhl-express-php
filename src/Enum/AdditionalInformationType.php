<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type of supplementary information requested in the create-shipment
 * response.
 *
 * Mirrors `getAdditionalInformation[].typeCode` (spec line 12688).
 */
enum AdditionalInformationType: string
{
    case PickupDetails = 'pickupDetails';
    case OptionalShipmentData = 'optionalShipmentData';
    case BarcodeInformation = 'barcodeInformation';
    case LinkLabelsByPieces = 'linkLabelsByPieces';
}

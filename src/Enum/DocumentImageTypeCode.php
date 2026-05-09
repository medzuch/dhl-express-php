<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Document type code for PLT (Paperless Trade) image uploads.
 *
 * Matches the `documentImages[].typeCode` enum in
 * `docs/dhl/dhl_openapi.yaml`. Used inside
 * {@see \Medzuch\DhlExpress\Dto\Shipment\DocumentImage} when
 * uploading customs documents via
 * {@see \Medzuch\DhlExpress\Api\ShipmentApi::uploadImage()}.
 */
enum DocumentImageTypeCode: string
{
    case INV = 'INV';
    case PNV = 'PNV';
    case COO = 'COO';
    case NAF = 'NAF';
    case CIN = 'CIN';
    case DCL = 'DCL';
    case AWB = 'AWB';
}

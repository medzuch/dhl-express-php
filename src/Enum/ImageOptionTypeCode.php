<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type codes for entries in `outputImageProperties.imageOptions`.
 *
 * Sourced from the OpenAPI `imageOptions.typeCode` enum under the
 * `supermodelIoLogisticsExpressOutputImageProperties` schema.
 *
 * Each code selects which document type an image option entry controls.
 * Use together with {@see OutputImageTemplate} for the `templateName`
 * of the corresponding entry.
 */
enum ImageOptionTypeCode: string
{
    case Label = 'label';
    case WaybillDoc = 'waybillDoc';
    case Invoice = 'invoice';
    /** The wire value contains a hyphen — PHP case name uses PascalCase. */
    case QrCode = 'qr-code';
    case ShipmentReceipt = 'shipmentReceipt';
    case Receipt = 'receipt';
}

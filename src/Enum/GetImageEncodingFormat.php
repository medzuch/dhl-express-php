<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Download format on `GET /shipments/{id}/get-image`.
 *
 * The OpenAPI `encodingFormat` query parameter enum constrains
 * this to two lowercase wire values: `pdf` or `tiff`. Distinct
 * from {@see LabelEncodingFormat} (label/waybill output) and
 * {@see DocumentImageFormat} (upload-side image format).
 */
enum GetImageEncodingFormat: string
{
    case Pdf = 'pdf';
    case Tiff = 'tiff';
}

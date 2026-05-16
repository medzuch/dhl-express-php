<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Image format for PLT (Paperless Trade) document uploads.
 *
 * Matches the `documentImages[].imageFormat` enum in
 * `specs/dhl/dhl_openapi.yaml`. Used inside
 * {@see \Medzuch\DhlExpress\Dto\Shipment\DocumentImage}.
 */
enum DocumentImageFormat: string
{
    case PDF = 'PDF';
    case PNG = 'PNG';
    case GIF = 'GIF';
    case TIFF = 'TIFF';
    case JPEG = 'JPEG';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * File format of a customer-supplied logo printed on a DHL transport
 * label.
 *
 * Mirrors `outputImageProperties.customerLogos[].fileFormat`
 * (spec line 10880).
 */
enum CustomerLogoFileFormat: string
{
    case PNG = 'PNG';
    case GIF = 'GIF';
    case JPEG = 'JPEG';
    case JPG = 'JPG';
}

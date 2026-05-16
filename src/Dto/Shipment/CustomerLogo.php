<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\CustomerLogoFileFormat;

/**
 * One entry in `outputImageProperties.customerLogos` (max 1 item).
 *
 * `$content` is base64-encoded image data up to 1 MiB.
 */
final readonly class CustomerLogo
{
    public function __construct(
        public CustomerLogoFileFormat $fileFormat,
        public string $content,
    ) {
    }

    /**
     * @return array{fileFormat: string, content: string}
     */
    public function toArray(): array
    {
        return [
            'fileFormat' => $this->fileFormat->value,
            'content' => $this->content,
        ];
    }
}

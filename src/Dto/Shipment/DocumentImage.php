<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\DocumentImageFormat;
use Medzuch\DhlExpress\Enum\DocumentImageTypeCode;

/**
 * A single base64-encoded customs document image for PLT upload.
 *
 * Used in {@see UploadImageRequest::$documentImages}.
 */
final readonly class DocumentImage
{
    public function __construct(
        public string $content,
        public DocumentImageTypeCode $typeCode = DocumentImageTypeCode::INV,
        public DocumentImageFormat $imageFormat = DocumentImageFormat::PDF,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'typeCode' => $this->typeCode->value,
            'imageFormat' => $this->imageFormat->value,
        ];
    }
}

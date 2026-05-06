<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Epod;

/**
 * One proof-of-delivery document inside an EPoD response.
 *
 * `content` is base64-encoded — call {@see decodeContent()} to get
 * the raw bytes ready to write to disk. `encodingFormat` is the file
 * format reported by DHL (e.g. `PDF`); `typeCode` is the document
 * classification (e.g. `POD`).
 */
final readonly class EpodDocument
{
    public function __construct(
        public string $encodingFormat,
        public string $content,
        public string $typeCode,
    ) {
    }

    public function decodeContent(): string
    {
        $decoded = base64_decode($this->content, true);

        return $decoded === false ? '' : $decoded;
    }
}

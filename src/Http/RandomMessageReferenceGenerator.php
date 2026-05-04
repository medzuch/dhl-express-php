<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Http;

use Medzuch\DhlExpress\ValueObject\MessageReference;

/**
 * Default {@see MessageReferenceGenerator} producing UUID v4 strings
 * via {@see random_bytes()}.
 *
 * The 36-character output fits DHL's 1-36 character header constraint
 * exactly and matches the example values in `dhl_openapi.yaml`.
 */
final class RandomMessageReferenceGenerator implements MessageReferenceGenerator
{
    public function generate(): MessageReference
    {
        $bytes = random_bytes(16);

        // RFC 4122 §4.4: set the version (4) and the IETF variant (10xx) bits.
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        $hex = bin2hex($bytes);

        return new MessageReference(sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ));
    }
}

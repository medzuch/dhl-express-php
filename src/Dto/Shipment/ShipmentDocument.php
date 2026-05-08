<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * One document returned in the create-shipment response (label,
 * waybill, invoice, receipt, or piece-level QR code).
 *
 * `content` is base64-encoded image data. `imageFormat` is typically
 * one of PDF / ZPL / EPL / LP2 / PNG depending on the request and the
 * document type. `typeCode` is `label` / `waybillDoc` / `invoice` /
 * `receipt` / `qr-code` / similar.
 */
final readonly class ShipmentDocument
{
    public function __construct(
        public string $imageFormat,
        public string $content,
        public string $typeCode,
    ) {
    }
}

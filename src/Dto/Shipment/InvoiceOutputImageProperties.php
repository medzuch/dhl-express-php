<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Optional invoice-image rendering controls on the invoice-upload
 * endpoints.
 *
 * Distinct from {@see OutputImageProperties} (used on create-shipment),
 * which exposes the full set of label/encoding knobs. The invoice
 * variant is narrower — the schema permits a single `imageOptions`
 * entry fixed to `typeCode=invoice`. Passing this block lets DHL
 * render and return a PDF/image of the uploaded invoice without a
 * separate `get-image` round-trip.
 */
final readonly class InvoiceOutputImageProperties
{
    /**
     * @param list<InvoiceImageOption> $imageOptions
     */
    public function __construct(
        public array $imageOptions = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->imageOptions !== []) {
            $payload['imageOptions'] = array_map(
                static fn (InvoiceImageOption $option): array => $option->toArray(),
                $this->imageOptions,
            );
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LabelEncodingFormat;

/**
 * Output formatting controls for shipment labels and documents.
 *
 * Mirrors `supermodelIoLogisticsExpressOutputImageProperties`. Phase 4a
 * exposes the most common knobs — the encoding format (PDF/ZPL/…), the
 * per-document `imageOptions` array, and a couple of boolean toggles.
 * Full label-template support (the deferred `OutputImageTemplate`
 * enum's ~66 templates) lands in Phase 4c.
 */
final readonly class OutputImageProperties
{
    /**
     * @param list<ImageOption> $imageOptions
     */
    public function __construct(
        public ?LabelEncodingFormat $encodingFormat = null,
        public array $imageOptions = [],
        public ?bool $renderDHLLogo = null,
        public ?bool $fitLabelsToA4 = null,
        public ?int $printerDPI = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->encodingFormat !== null) {
            $payload['encodingFormat'] = $this->encodingFormat->value;
        }
        if ($this->imageOptions !== []) {
            $payload['imageOptions'] = array_map(
                static fn (ImageOption $option): array => $option->toArray(),
                $this->imageOptions,
            );
        }
        if ($this->renderDHLLogo !== null) {
            $payload['renderDHLLogo'] = $this->renderDHLLogo;
        }
        if ($this->fitLabelsToA4 !== null) {
            $payload['fitLabelsToA4'] = $this->fitLabelsToA4;
        }
        if ($this->printerDPI !== null) {
            $payload['printerDPI'] = $this->printerDPI;
        }

        return $payload;
    }
}

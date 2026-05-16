<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LabelEncodingFormat;

/**
 * Output formatting controls for shipment labels and documents.
 *
 * Mirrors `supermodelIoLogisticsExpressOutputImageProperties`. Carries
 * encoding format, per-document `imageOptions[]`, and `printerDPI`.
 *
 * Note: `renderDHLLogo` and `fitLabelsToA4` are per-document toggles
 * and live on {@see ImageOption}, not here (spec lines 11021–11037).
 */
final readonly class OutputImageProperties
{
    /**
     * @param list<ImageOption> $imageOptions
     */
    public function __construct(
        public ?LabelEncodingFormat $encodingFormat = null,
        public array $imageOptions = [],
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
        if ($this->printerDPI !== null) {
            $payload['printerDPI'] = $this->printerDPI;
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LabelEncodingFormat;

/**
 * One entry in `outputImageProperties.imageOptions`.
 *
 * Mirrors the `imageOptions` array item under the
 * `supermodelIoLogisticsExpressOutputImageProperties` schema. The
 * `renderDHLLogo` and `fitLabelsToA4` toggles live here (not on the
 * root `outputImageProperties` block) — they are per-document, e.g.
 * a single shipment may want a DHL logo on the label but not on the
 * waybill (spec lines 11021–11037).
 */
final readonly class ImageOption
{
    public function __construct(
        public string $typeCode,
        public ?bool $isRequested = null,
        public ?string $templateName = null,
        public ?LabelEncodingFormat $encodingFormat = null,
        public ?bool $hideAccountNumber = null,
        public ?int $numberOfCopies = null,
        public ?bool $renderDHLLogo = null,
        public ?bool $fitLabelsToA4 = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'typeCode' => $this->typeCode,
        ];

        if ($this->isRequested !== null) {
            $payload['isRequested'] = $this->isRequested;
        }
        if ($this->templateName !== null) {
            $payload['templateName'] = $this->templateName;
        }
        if ($this->encodingFormat !== null) {
            $payload['encodingFormat'] = $this->encodingFormat->value;
        }
        if ($this->hideAccountNumber !== null) {
            $payload['hideAccountNumber'] = $this->hideAccountNumber;
        }
        if ($this->numberOfCopies !== null) {
            $payload['numberOfCopies'] = $this->numberOfCopies;
        }
        if ($this->renderDHLLogo !== null) {
            $payload['renderDHLLogo'] = $this->renderDHLLogo;
        }
        if ($this->fitLabelsToA4 !== null) {
            $payload['fitLabelsToA4'] = $this->fitLabelsToA4;
        }

        return $payload;
    }
}

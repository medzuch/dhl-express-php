<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LabelEncodingFormat;

/**
 * One entry in `outputImageProperties.imageOptions`.
 *
 * Mirrors the `imageOptions` array item under the
 * `supermodelIoLogisticsExpressOutputImageProperties` schema. `typeCode`
 * is kept as a string in Phase 4a — the value space (`label`,
 * `waybillDoc`, `invoice`, `qr-code`, `shipmentReceipt`) may become an
 * enum in Phase 4c when label-template support lands.
 *
 * `templateName` is also a free string in 4a; the
 * `OutputImageTemplate` enum (~66 templates from the workbook) ships in
 * Phase 4c at the point of consumption.
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

        return $payload;
    }
}

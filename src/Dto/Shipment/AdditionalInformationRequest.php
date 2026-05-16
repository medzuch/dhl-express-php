<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\AdditionalInformationType;

/**
 * One entry in `getAdditionalInformation` requesting supplementary
 * information in the create-shipment response.
 *
 * Mirrors an item of `getAdditionalInformation[]` (spec lines
 * 12675–12699). Up to 5 entries allowed.
 */
final readonly class AdditionalInformationRequest
{
    public function __construct(
        public AdditionalInformationType $typeCode,
        public bool $isRequested,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode->value,
            'isRequested' => $this->isRequested,
        ];
    }
}

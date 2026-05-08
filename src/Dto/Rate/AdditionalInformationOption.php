<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\AdditionalInformationTypeCode;

/**
 * One element of the `getAdditionalInformation` array on a rate
 * request — picks an extra block (all VAS, VAS + rule groups, sort
 * codes) and toggles its inclusion.
 */
final readonly class AdditionalInformationOption
{
    public function __construct(
        public AdditionalInformationTypeCode $typeCode,
        public bool $isRequested,
    ) {
    }

    /**
     * @return array{typeCode: string, isRequested: bool}
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode->value,
            'isRequested' => $this->isRequested,
        ];
    }
}

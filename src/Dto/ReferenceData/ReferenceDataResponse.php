<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ReferenceData;

/**
 * Response from `GET /reference-data`.
 *
 * Holds the list of returned datasets (`referenceData`) and any
 * DHL-side warnings.
 */
final readonly class ReferenceDataResponse
{
    /**
     * @param list<ReferenceData> $referenceData
     * @param list<string>        $warnings
     */
    public function __construct(
        public array $referenceData,
        public array $warnings,
    ) {
    }
}

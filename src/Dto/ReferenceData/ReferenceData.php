<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ReferenceData;

/**
 * One dataset's worth of reference-data rows.
 *
 * `data` is a list of rows; each row is itself a list of
 * {@see ReferenceDataAttribute} pairs. Order is preserved as
 * returned by DHL.
 */
final readonly class ReferenceData
{
    /**
     * @param list<list<ReferenceDataAttribute>> $data
     */
    public function __construct(
        public string $datasetName,
        public string $dataSetCaptions,
        public array $data,
    ) {
    }
}

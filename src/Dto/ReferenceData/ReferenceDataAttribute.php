<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ReferenceData;

/**
 * One attribute / value pair within a reference-data row.
 *
 * DHL returns each record as an ordered list of these pairs
 * (e.g. `attribute: countryCode, value: GD`).
 */
final readonly class ReferenceDataAttribute
{
    public function __construct(
        public string $attribute,
        public string $value,
    ) {
    }
}

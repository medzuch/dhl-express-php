<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Identifier;

/**
 * Response from `GET /identifiers`.
 *
 * Carries any DHL-side warnings and the allocated identifier
 * groups. Most calls produce one group, but the schema is a list
 * because DHL reserves the right to expand the response shape if
 * an account is configured to receive multiple types at once.
 */
final readonly class IdentifierResponse
{
    /**
     * @param list<string>          $warnings
     * @param list<IdentifierGroup> $identifiers
     */
    public function __construct(
        public array $warnings,
        public array $identifiers,
    ) {
    }
}

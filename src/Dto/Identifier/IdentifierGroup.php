<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Identifier;

use Medzuch\DhlExpress\Enum\IdentifierType;

/**
 * One typeCode-bucketed group inside an {@see IdentifierResponse}.
 *
 * DHL groups the allocated identifiers by `typeCode` even when only
 * one type was requested — each group carries the matching list of
 * pre-allocated values.
 */
final readonly class IdentifierGroup
{
    /**
     * @param list<string> $list
     */
    public function __construct(
        public IdentifierType $typeCode,
        public array $list,
    ) {
    }
}

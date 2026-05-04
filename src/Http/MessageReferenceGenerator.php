<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Http;

use Medzuch\DhlExpress\ValueObject\MessageReference;

/**
 * Produces a fresh {@see MessageReference} for each outbound request.
 *
 * Exists as an interface so tests and consumers can substitute a
 * deterministic generator without touching the underlying RNG.
 */
interface MessageReferenceGenerator
{
    public function generate(): MessageReference;
}

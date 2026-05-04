<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * DHL Express shipment tracking number (the value placed in the
 * `shipmentTrackingNumber` path segment).
 *
 * The OpenAPI spec types this as a free-form string, so we only
 * enforce non-emptiness — DHL surfaces format mismatches as a 404
 * which the error mapper translates into DhlNotFoundException.
 */
final readonly class TrackingNumber implements Stringable
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Tracking number must not be empty');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

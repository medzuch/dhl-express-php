<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use DateTimeInterface;
use InvalidArgumentException;
use Stringable;

/**
 * Year-month in `YYYY-MM` form.
 *
 * Used by `GET /shipments/{id}/get-image` for the
 * `pickupYearAndMonth` query parameter, where DHL requires the
 * shipment's pickup month to look up archived documents.
 */
final readonly class YearMonth implements Stringable
{
    public function __construct(public string $value)
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) !== 1) {
            throw new InvalidArgumentException(
                "Year-month must match 'YYYY-MM'; got '{$value}'.",
            );
        }
    }

    public static function fromDateTime(DateTimeInterface $dateTime): self
    {
        return new self($dateTime->format('Y-m'));
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

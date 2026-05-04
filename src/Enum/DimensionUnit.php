<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Concrete linear-dimension unit a
 * {@see \Medzuch\DhlExpress\ValueObject\Dimensions} is expressed in.
 *
 * The backing value is the unit symbol itself (CM, IN); the wire
 * format DHL stores is the parent {@see UnitSystem}, exposed via
 * `->system()`.
 */
enum DimensionUnit: string
{
    case CM = 'CM';
    case IN = 'IN';

    public function system(): UnitSystem
    {
        return match ($this) {
            self::CM => UnitSystem::Metric,
            self::IN => UnitSystem::Imperial,
        };
    }
}

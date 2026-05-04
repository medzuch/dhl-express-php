<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Concrete weight unit a {@see \Medzuch\DhlExpress\ValueObject\Weight}
 * is expressed in.
 *
 * The backing value is the unit symbol itself (KG, LB) so it stays
 * meaningful at a glance. DHL only writes the parent {@see UnitSystem}
 * to the wire, so call `->system()` when serializing.
 */
enum WeightUnit: string
{
    case KG = 'KG';
    case LB = 'LB';

    public function system(): UnitSystem
    {
        return match ($this) {
            self::KG => UnitSystem::Metric,
            self::LB => UnitSystem::Imperial,
        };
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Lowercase y/n flag used by several `/servicepoints` query parameters
 * (`importCharges`, `excludeFullyBooked`, `encrypt`). Case-sensitive.
 */
enum YesNoIndicator: string
{
    case Yes = 'y';
    case No = 'n';
}

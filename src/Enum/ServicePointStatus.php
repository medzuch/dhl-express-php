<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Service point status filter for the `/servicepoints` `svpStatus`
 * query parameter. `A` is active; `S`, `U`, `X`, `Y` represent
 * various inactive states. DHL's API is case-sensitive and only
 * accepts uppercase values.
 */
enum ServicePointStatus: string
{
    case Active = 'A';
    case S = 'S';
    case U = 'U';
    case X = 'X';
    case Y = 'Y';
}

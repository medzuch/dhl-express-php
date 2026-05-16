<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Position of a bespoke text entry on the transport label.
 *
 * Mirrors `package.labelText[].position` (spec line 14792). The six
 * slots run top-to-bottom on each side of the label.
 */
enum LabelTextPosition: string
{
    case Left1 = 'left1';
    case Left2 = 'left2';
    case Left3 = 'left3';
    case Right1 = 'right1';
    case Right2 = 'right2';
    case Right3 = 'right3';
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Dangerous-goods content identifier.
 *
 * The 20 content classifications listed in Reference Data Guide
 * section 5, ranging from biological substances and dry ice to the
 * various lithium battery packaging instructions. Wire codes that
 * start with a digit get descriptive PascalCase case names (PHP
 * does not allow case names to start with a digit); alpha-prefixed
 * codes keep their wire identifiers.
 *
 * Each content id is paired with a {@see DangerousGoodsServiceCode}
 * — that mapping lives in the reference PDF table, not here.
 */
enum DangerousGoodsContentId: string
{
    case BiologicalSubstanceUN3373 = '650';
    case A01 = 'A01';
    case A02 = 'A02';
    case GeneticallyModifiedOrganisms = '651';
    case HU1 = 'HU1';
    case HU4 = 'HU4';
    case HU3 = 'HU3';
    case HT1 = 'HT1';
    case DryIceUN1845 = '901';
    case E01 = 'E01';
    case HU5 = 'HU5';
    case DangerousGoodsPax = '910';
    case DangerousGoodsCao = '911';
    case ConsumerGoodsId8000 = '700';
    case LithiumMetalPI970 = '970';
    case LithiumMetalPI969 = '969';
    case LithiumIonPI966 = '966';
    case LithiumIonPI967 = '967';
    case HU2 = 'HU2';
    case HU6 = 'HU6';
}

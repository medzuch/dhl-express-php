<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Physical process capability codes used by the `/servicepoints`
 * `capability` query parameter.
 *
 * Each code names a specific customer-facing flow a service point
 * supports. The spec lists allowed combinations:
 * 81,73 | 81,74 | 81,75,76 | 83,74 | 83,75,76 | 82,74 | 82,75,76 |
 * 88,73 | 78,79 | 86,87. Combination rules are enforced by the
 * {@see \Medzuch\DhlExpress\Builder\ServicePointFindCriteriaBuilder},
 * not this enum.
 */
enum ServicePointCapability: string
{
    case HasDhlAccountOrReturn = '81';
    case PaidOnline = '82';
    case ReturnShipment = '83';
    case WillPayAtServicePoint = '88';
    case WillCreateLabelAtServicePoint = '73';
    case HasPrintedLabel = '74';
    case HasQrCode1 = '75';
    case HasQrCode2 = '76';
    case CollectingParcel1 = '78';
    case CollectingParcel2 = '79';
    case DirectedParcel1 = '86';
    case DirectedParcel2 = '87';
}

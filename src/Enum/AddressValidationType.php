<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Direction of address validation against DHL Express capabilities.
 *
 * `Pickup` checks whether DHL can collect from the address;
 * `Delivery` checks whether DHL can deliver to it. The two values
 * come from the OpenAPI inline `enum` on the address-validate
 * `type` query parameter.
 */
enum AddressValidationType: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';
}

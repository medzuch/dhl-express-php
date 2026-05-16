<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Recipient of a neighbour-delivery handoff under the On Demand
 * Delivery service.
 *
 * Mirrors `onDemandDelivery.whereToLeave` (spec line 12491).
 */
enum OnDemandWhereToLeave: string
{
    case Concierge = 'concierge';
    case Neighbour = 'neighbour';
}

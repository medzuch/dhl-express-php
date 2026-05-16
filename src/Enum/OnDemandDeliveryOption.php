<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Delivery option for the DHL Express On Demand Delivery (ODD) service.
 *
 * Mirrors `onDemandDelivery.deliveryOption` (spec line 12455).
 */
enum OnDemandDeliveryOption: string
{
    case Servicepoint = 'servicepoint';
    case Neighbour = 'neighbour';
    case SignatureRelease = 'signatureRelease';
}

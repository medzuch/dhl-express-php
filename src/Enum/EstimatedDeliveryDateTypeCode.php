<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Estimated Delivery Date variant requested from DHL.
 *
 * - `QDDC` (DHL service commitment) builds in clearance and other
 *   non-transport components.
 * - `QDDF` (fastest transit) is the optimistic transit-only quote;
 *   when clearance is expected to delay delivery, QDDF does not
 *   constitute DHL's delivery commitment.
 *
 * Source: `supermodelIoLogisticsExpressRateRequest.estimatedDeliveryDate.typeCode`
 * in `specs/dhl/dhl_openapi.yaml`.
 */
enum EstimatedDeliveryDateTypeCode: string
{
    case QDDC = 'QDDC';
    case QDDF = 'QDDF';
}

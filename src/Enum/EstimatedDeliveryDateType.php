<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * EDD (Estimated Delivery Date) type.
 *
 * Mirrors `estimatedDeliveryDate.typeCode` (spec line 12660).
 *
 * - `QDDC` — Quoted Delivery Date as Customer-committed; DHL's
 *   service-commitment date built in clearance/operational buffers.
 * - `QDDF` — Quoted Delivery Date as Fastest-transit; raw transit time
 *   that doesn't promise customs/clearance throughput.
 */
enum EstimatedDeliveryDateType: string
{
    case QDDC = 'QDDC';
    case QDDF = 'QDDF';
}

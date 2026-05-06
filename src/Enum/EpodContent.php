<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Variant of electronic-proof-of-delivery the API should return.
 *
 * Backed by the OpenAPI inline `enum` on the
 * `GET /shipments/{shipmentTrackingNumber}/proof-of-delivery`
 * `content` query parameter. DHL defaults to `epod-summary` when
 * the parameter is omitted.
 *
 * - `*-summary` — POD-only document, smaller.
 * - `*-detail`  — POD plus shipment details.
 * - `*-table`   — tabular POD layout.
 * - `*-esig`    — variant including the recipient's e-signature image.
 */
enum EpodContent: string
{
    case Detail = 'epod-detail';
    case Summary = 'epod-summary';
    case DetailEsig = 'epod-detail-esig';
    case SummaryEsig = 'epod-summary-esig';
    case Table = 'epod-table';
    case TableDetail = 'epod-table-detail';
    case TableEsig = 'epod-table-esig';
}

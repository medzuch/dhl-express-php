<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Reference type code attached to an individual package on a
 * shipment.
 *
 * Fourteen codes covering shipping-leg references (consignor,
 * receiver, freight forwarder, freight bill, inbound/outbound
 * centre), local payer/shipper/receiver account numbers, customs
 * declaration number, and the eurolog 15-digit shipment id. CU
 * (consignor reference) is the DHL default.
 *
 * Distinct from {@see InvoiceReferenceTypeCode} (invoice-level)
 * and {@see LineItemReferenceTypeCode} (line-item-level) — the
 * value space is its own.
 */
enum PackageReferenceTypeCode: string
{
    case AAO = 'AAO';
    case CU = 'CU';
    case FF = 'FF';
    case FN = 'FN';
    case IBC = 'IBC';
    case LLR = 'LLR';
    case OBC = 'OBC';
    case PRN = 'PRN';
    case ACP = 'ACP';
    case ACS = 'ACS';
    case ACR = 'ACR';
    case CDN = 'CDN';
    case STD = 'STD';
    case CO = 'CO';
}

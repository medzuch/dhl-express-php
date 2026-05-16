<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Business-party type code as used by the invoice-upload endpoints.
 *
 * The invoice-upload schemas (`supermodelIoLogisticsExpressUploadInvoiceDataRequest`
 * and `…RequestSID`) accept a different value space than the
 * shipment-level {@see BusinessPartyTypeCode}: lowercase full-word
 * strings (`business`, `direct_consumer`, …) rather than the
 * two-letter shipment codes (`BU`, `DC`, …). Same conceptual slot,
 * different wire vocabulary — DHL maintains both.
 */
enum InvoicePartyTypeCode: string
{
    case Business = 'business';
    case DirectConsumer = 'direct_consumer';
    case Government = 'government';
    case Other = 'other';
    case Private = 'private';
    case Reseller = 'reseller';
}

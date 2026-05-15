<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Document types selectable on `GET /shipments/{id}/get-image`.
 *
 * Backing values are kebab-case wire codes from the OpenAPI
 * `typeCode` query parameter enum. Distinct from
 * {@see DocumentImageTypeCode}, which is the upload-side
 * (`PATCH /upload-image`) three-letter enum (INV, AWB, COO, ...).
 */
enum GetImageDocumentTypeCode: string
{
    case Waybill = 'waybill';
    case CommercialInvoice = 'commercial-invoice';
    case CustomsEntry = 'customs-entry';
    case TransportAccompanyingDocument = 'transport-accompanying-document';
    case GenericEntrySummary = 'generic-entry-summary';
    case DhlIssuedProformaInvoice = 'dhl-issued-proforma-invoice';
}

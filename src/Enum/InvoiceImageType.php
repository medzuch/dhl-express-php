<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Customs-document type printed on the rendered invoice image.
 *
 * Mirrors the `invoiceType` enum under
 * `outputImageProperties.imageOptions[].invoiceType`
 * (spec line 10990–10996).
 *
 * Distinct from {@see InvoicePartyTypeCode} (party role on an
 * invoice) and from the upload-invoice flow's own template selection.
 */
enum InvoiceImageType: string
{
    case Commercial = 'commercial';
    case Proforma = 'proforma';
    case Returns = 'returns';
}

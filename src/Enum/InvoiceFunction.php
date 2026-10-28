<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Whether an invoice serves an import, export, or both directions.
 *
 * Matches the `invoice.function` enum in `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`.
 * Used inside {@see \Medzuch\DhlExpress\Dto\Shipment\ExportInvoice}.
 */
enum InvoiceFunction: string
{
    case Import = 'import';
    case Export = 'export';
    case Both = 'both';
}

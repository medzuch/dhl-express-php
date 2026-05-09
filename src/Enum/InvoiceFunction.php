<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Whether an invoice serves an import, export, or both directions.
 *
 * Matches the `invoice.function` enum in `docs/dhl/dhl_openapi.yaml`.
 * Used inside {@see \Medzuch\DhlExpress\Dto\Shipment\ExportInvoice}.
 */
enum InvoiceFunction: string
{
    case Import = 'import';
    case Export = 'export';
    case Both = 'both';
}

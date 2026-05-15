<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Direction of the document returned by
 * `GET /shipments/{id}/get-image`.
 *
 * Returned in `DocumentImageResult.function` for some `typeCode`s
 * (e.g. customs-entry); absent for others (e.g. waybill).
 */
enum DocumentFunction: string
{
    case Import = 'import';
    case Export = 'export';
    case Both = 'both';
}

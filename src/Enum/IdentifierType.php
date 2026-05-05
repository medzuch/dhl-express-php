<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type of identifier the `/identifiers` endpoint can pre-allocate
 * for an account.
 *
 * The seven values from the OpenAPI inline `enum` on the
 * `identifierType` query parameter — used for DHL Express
 * Breakbulk and Loose Break Bulk shipments where identifiers must
 * be reserved upfront. SID is the most common (Shipment Identifier);
 * ASID3..ASID24 cover the size-bucketed Air Shipment Identifier
 * variants.
 */
enum IdentifierType: string
{
    case SID = 'SID';
    case PID = 'PID';
    case ASID3 = 'ASID3';
    case ASID6 = 'ASID6';
    case ASID12 = 'ASID12';
    case ASID24 = 'ASID24';
    case HUID = 'HUID';
}

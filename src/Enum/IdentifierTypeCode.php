<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type of a waybill `identifier` entry.
 *
 * Mirrors `supermodelIoLogisticsExpressIdentifier.typeCode`
 * (spec line 14132).
 */
enum IdentifierTypeCode: string
{
    case ParentId = 'parentId';
    case ShipmentId = 'shipmentId';
    case PieceId = 'pieceId';
}

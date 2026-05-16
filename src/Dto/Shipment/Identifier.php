<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\IdentifierTypeCode;

/**
 * One entry in a shipment- or package-level `identifiers` array.
 *
 * Mirrors `supermodelIoLogisticsExpressIdentifier`. DHL accepts up to
 * 5 identifiers at shipment level and up to 3 per package; the per-
 * shipment / per-package caps live with the container, not this DTO.
 *
 * The `dataIdentifier` field is required for `pieceId` entries when
 * the caller's IT setup requires piece-level identification.
 */
final readonly class Identifier
{
    public function __construct(
        public IdentifierTypeCode $typeCode,
        public string $value,
        public ?string $dataIdentifier = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = [
            'typeCode' => $this->typeCode->value,
            'value' => $this->value,
        ];

        if ($this->dataIdentifier !== null) {
            $payload['dataIdentifier'] = $this->dataIdentifier;
        }

        return $payload;
    }
}

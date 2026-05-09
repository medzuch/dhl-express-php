<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\DangerousGoodsContentId;

/**
 * Dangerous-goods block on a create-shipment request.
 *
 * DHL accepts an array of at most one item for this block —
 * {@see \Medzuch\DhlExpress\Dto\Shipment\CreateShipmentRequest::toArray()}
 * wraps this single DTO in an array when serialising.
 *
 * `contentId` is always required. All other fields are optional.
 */
final readonly class DangerousGoods
{
    /**
     * @param list<string> $unCodes
     */
    public function __construct(
        public DangerousGoodsContentId $contentId,
        public ?float $dryIceTotalNetWeight = null,
        public ?string $customDescription = null,
        public array $unCodes = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = ['contentId' => $this->contentId->value];

        if ($this->dryIceTotalNetWeight !== null) {
            $payload['dryIceTotalNetWeight'] = $this->dryIceTotalNetWeight;
        }
        if ($this->customDescription !== null) {
            $payload['customDescription'] = $this->customDescription;
        }
        if ($this->unCodes !== []) {
            $payload['unCodes'] = $this->unCodes;
        }

        return $payload;
    }
}

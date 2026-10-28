<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\DangerousGoodsContentId;
use Medzuch\DhlExpress\Enum\UnitSystem;

/**
 * Dangerous-goods block on a create-shipment request.
 *
 * DHL accepts an array of at most one item for this block —
 * {@see \Medzuch\DhlExpress\Dto\Shipment\CreateShipmentRequest::toArray()}
 * wraps this single DTO in an array when serialising.
 *
 * `contentId` is always required. All other fields are optional:
 * `customDescription` (≤ 1000 chars), `packagesCount` (1–999, the
 * number of packages holding this classification) and
 * `dryIceUnitOfMeasurement` (the unit of `dryIceTotalNetWeight`,
 * independent of the shipment-level unit) were added in MyDHL API 3.4.0.
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
        public ?int $packagesCount = null,
        public ?UnitSystem $dryIceUnitOfMeasurement = null,
    ) {
        if ($customDescription !== null && strlen($customDescription) > 1000) {
            throw new \InvalidArgumentException('DangerousGoods customDescription must be at most 1000 characters.');
        }
        if ($packagesCount !== null && ($packagesCount < 1 || $packagesCount > 999)) {
            throw new \InvalidArgumentException('DangerousGoods packagesCount must be between 1 and 999.');
        }
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
        if ($this->packagesCount !== null) {
            $payload['packagesCount'] = $this->packagesCount;
        }
        if ($this->dryIceUnitOfMeasurement !== null) {
            $payload['dryIceUnitOfMeasurement'] = $this->dryIceUnitOfMeasurement->value;
        }

        return $payload;
    }
}

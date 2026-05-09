<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

/**
 * A single special instruction for the courier at pickup.
 *
 * Mirrors the `specialInstructions` item schema in
 * `supermodelIoLogisticsExpressPickupRequest`. `typeCode` is marked
 * "for future use" by DHL; include it when provided but do not
 * require it.
 */
final readonly class PickupSpecialInstruction
{
    public function __construct(
        public string $value,
        public ?string $typeCode = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = ['value' => $this->value];

        if ($this->typeCode !== null) {
            $payload['typeCode'] = $this->typeCode;
        }

        return $payload;
    }
}

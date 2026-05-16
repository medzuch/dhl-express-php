<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * A pre-paid charge declared against a create-shipment request.
 *
 * Mirrors an item of `prepaidCharges[]` (spec lines 12600–12643).
 * DHL accepts at most one entry, and currently only `freight`
 * (paid in `cash`) is supported, so the DTO encodes both as literal
 * defaults.
 */
final readonly class PrepaidCharge
{
    public function __construct(
        public CurrencyCode $currency,
        public float $value,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'typeCode' => 'freight',
            'currency' => $this->currency->value,
            'value' => $this->value,
            'method' => 'cash',
        ];
    }
}

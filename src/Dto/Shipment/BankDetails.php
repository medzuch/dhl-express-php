<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * One entry in a party's `bankDetails` array.
 *
 * Mirrors an item of `supermodelIoLogisticsExpressBankDetails`. DHL
 * accepts at most one bank record per party; all three fields are
 * optional but at least one must be set (the spec enforces
 * `minProperties: 1`).
 *
 * Today this is primarily used for Russia commercial-invoice mapping
 * (bank name + settlement currency).
 */
final readonly class BankDetails
{
    public function __construct(
        public ?string $name = null,
        public ?string $settlementLocalCurrency = null,
        public ?string $settlementForeignCurrency = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->name !== null) {
            $payload['name'] = $this->name;
        }
        if ($this->settlementLocalCurrency !== null) {
            $payload['settlementLocalCurrency'] = $this->settlementLocalCurrency;
        }
        if ($this->settlementForeignCurrency !== null) {
            $payload['settlementForeignCurrency'] = $this->settlementForeignCurrency;
        }

        return $payload;
    }
}

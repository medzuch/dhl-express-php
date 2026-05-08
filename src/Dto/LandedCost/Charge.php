<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\LandedCost;

use Medzuch\DhlExpress\Enum\ChargeTypeCode;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * Additional charge included in a landed-cost calculation —
 * freight, insurance, or any other line.
 */
final readonly class Charge
{
    public function __construct(
        public ChargeTypeCode $typeCode,
        public float $amount,
        public CurrencyCode $currencyCode,
    ) {
    }

    /**
     * @return array{typeCode: string, amount: float, currencyCode: string}
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode->value,
            'amount' => $this->amount,
            'currencyCode' => $this->currencyCode->value,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\Enum\MonetaryAmountTypeCode;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * One declared- or insured-value entry attached to a rate request.
 *
 * Mirrors the inline `monetaryAmount[]` items in
 * `supermodelIoLogisticsExpressRateRequest`.
 */
final readonly class MonetaryAmount
{
    public function __construct(
        public MonetaryAmountTypeCode $typeCode,
        public float $value,
        public CurrencyCode $currency,
    ) {
    }

    /**
     * @return array{typeCode: string, value: float, currency: string}
     */
    public function toArray(): array
    {
        return [
            'typeCode' => $this->typeCode->value,
            'value' => $this->value,
            'currency' => $this->currency->value,
        ];
    }
}

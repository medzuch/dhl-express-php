<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Pre-calculated totals for the entire export invoice.
 *
 * Both fields are required when this block is present (min 0).
 */
final readonly class PreCalculatedTotalValues
{
    public function __construct(
        public float $preCalculatedTotalGoodsValue,
        public float $preCalculatedTotalInvoiceValue,
    ) {
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'preCalculatedTotalGoodsValue' => $this->preCalculatedTotalGoodsValue,
            'preCalculatedTotalInvoiceValue' => $this->preCalculatedTotalInvoiceValue,
        ];
    }
}

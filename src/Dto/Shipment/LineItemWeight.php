<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Weight sub-object of an export line item.
 *
 * At least one of `netValue` or `grossValue` must be provided. Both
 * values follow the constraints `min: 0`, `max: 999999999999`,
 * `multipleOf: 0.001`.
 */
final readonly class LineItemWeight
{
    public function __construct(
        public ?float $netValue = null,
        public ?float $grossValue = null,
    ) {
        if ($this->netValue === null && $this->grossValue === null) {
            throw new \InvalidArgumentException(
                'LineItemWeight requires at least one of netValue or grossValue.',
            );
        }
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        $result = [];

        if ($this->netValue !== null) {
            $result['netValue'] = $this->netValue;
        }
        if ($this->grossValue !== null) {
            $result['grossValue'] = $this->grossValue;
        }

        return $result;
    }
}

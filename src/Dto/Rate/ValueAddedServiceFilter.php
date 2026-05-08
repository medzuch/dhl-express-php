<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * Filter entry used to scope a rate response to specific value-added
 * services. The `serviceCode` is the global VAS code (e.g. `II` for
 * insurance, `WY` for paperless trade); `localServiceCode` is the
 * country-specific override.
 *
 * Optional `value` and `currency` carry monetary amounts associated
 * with the VAS (e.g. insured value).
 */
final readonly class ValueAddedServiceFilter
{
    public function __construct(
        public string $serviceCode,
        public ?string $localServiceCode = null,
        public ?float $value = null,
        public ?CurrencyCode $currency = null,
        public ?string $dgContent = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = ['serviceCode' => $this->serviceCode];

        if ($this->localServiceCode !== null) {
            $payload['localServiceCode'] = $this->localServiceCode;
        }
        if ($this->value !== null) {
            $payload['value'] = $this->value;
        }
        if ($this->currency !== null) {
            $payload['currency'] = $this->currency->value;
        }
        if ($this->dgContent !== null) {
            $payload['dgContent'] = $this->dgContent;
        }

        return $payload;
    }
}

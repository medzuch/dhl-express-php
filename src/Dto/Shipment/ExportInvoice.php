<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Enum\InvoiceFunction;

/**
 * Invoice block inside an export declaration.
 *
 * `number` is required (1–35 chars). `date` is serialized as
 * `YYYY-MM-DD`. `function` describes whether the invoice is for an
 * import, export, or both directions.
 */
final readonly class ExportInvoice
{
    /**
     * @param list<InvoiceReference> $customerReferences
     */
    public function __construct(
        public string $number,
        public DateTimeImmutable $date,
        public InvoiceFunction $function,
        public array $customerReferences = [],
        public ?IndicativeCustomsValues $indicativeCustomsValues = null,
        public ?PreCalculatedTotalValues $preCalculatedTotalValues = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'number' => $this->number,
            'date' => $this->date->format('Y-m-d'),
            'function' => $this->function->value,
        ];

        if ($this->customerReferences !== []) {
            $payload['customerReferences'] = array_map(
                static fn (InvoiceReference $ref): array => $ref->toArray(),
                $this->customerReferences,
            );
        }
        if ($this->indicativeCustomsValues !== null) {
            $payload['indicativeCustomsValues'] = $this->indicativeCustomsValues->toArray();
        }
        if ($this->preCalculatedTotalValues !== null) {
            $payload['preCalculatedTotalValues'] = $this->preCalculatedTotalValues->toArray();
        }

        return $payload;
    }
}

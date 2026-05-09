<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\ExportReasonType;

/**
 * A single line item within an export declaration.
 *
 * Required fields: `number` (1–1000), `description` (max 512 chars),
 * `price` (min 0), `quantity`, `manufacturerCountry` (2-char ISO),
 * `weight`.
 *
 * Optional fields: `exportReasonType`, `commodityCodes` (max 2),
 * `isTaxesPaid`, `customerReferences` (max 100),
 * `customsDocuments` (max 50),
 * `preCalculatedLineItemTotalValue`.
 */
final readonly class ExportLineItem
{
    /**
     * @param list<CommodityCode>          $commodityCodes
     * @param list<LineItemReference>      $customerReferences
     * @param list<LineItemCustomsDocument> $customsDocuments
     */
    public function __construct(
        public int $number,
        public string $description,
        public float $price,
        public LineItemQuantity $quantity,
        public string $manufacturerCountry,
        public LineItemWeight $weight,
        public ?ExportReasonType $exportReasonType = null,
        public array $commodityCodes = [],
        public ?bool $isTaxesPaid = null,
        public array $customerReferences = [],
        public array $customsDocuments = [],
        public ?float $preCalculatedLineItemTotalValue = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'number' => $this->number,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity->toArray(),
            'manufacturerCountry' => $this->manufacturerCountry,
            'weight' => $this->weight->toArray(),
        ];

        if ($this->exportReasonType !== null) {
            $payload['exportReasonType'] = $this->exportReasonType->value;
        }
        if ($this->commodityCodes !== []) {
            $payload['commodityCodes'] = array_map(
                static fn (CommodityCode $code): array => $code->toArray(),
                $this->commodityCodes,
            );
        }
        if ($this->isTaxesPaid !== null) {
            $payload['isTaxesPaid'] = $this->isTaxesPaid;
        }
        if ($this->customerReferences !== []) {
            $payload['customerReferences'] = array_map(
                static fn (LineItemReference $ref): array => $ref->toArray(),
                $this->customerReferences,
            );
        }
        if ($this->customsDocuments !== []) {
            $payload['customsDocuments'] = array_map(
                static fn (LineItemCustomsDocument $doc): array => $doc->toArray(),
                $this->customsDocuments,
            );
        }
        if ($this->preCalculatedLineItemTotalValue !== null) {
            $payload['preCalculatedLineItemTotalValue'] = $this->preCalculatedLineItemTotalValue;
        }

        return $payload;
    }
}

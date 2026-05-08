<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\LandedCost;

use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\CustomerDetails;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Enum\MerchantCarrier;
use Medzuch\DhlExpress\Enum\ShipmentPurpose;
use Medzuch\DhlExpress\Enum\TransportationMode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * Top-level POST `/landed-cost` request body.
 *
 * Mirrors `supermodelIoLogisticsExpressLandedCostRequest`. The DHL
 * spec stamps `customerDetails`, `accounts`, `unitOfMeasurement`,
 * `currencyCode`, `isCustomsDeclarable`, `getCostBreakdown`,
 * `packages`, and `items` as required — those are non-optional
 * constructor parameters.
 *
 * Construct directly, or via {@see \Medzuch\DhlExpress\Builder\LandedCostRequestBuilder}.
 */
final readonly class LandedCostRequest
{
    /**
     * @param list<Account>     $accounts
     * @param list<RatePackage> $packages
     * @param list<LineItem>    $items
     * @param list<Charge>      $charges
     */
    public function __construct(
        public CustomerDetails $customerDetails,
        public array $accounts,
        public UnitSystem $unitOfMeasurement,
        public CurrencyCode $currencyCode,
        public bool $isCustomsDeclarable,
        public bool $getCostBreakdown,
        public array $packages,
        public array $items,
        public ?string $productCode = null,
        public ?string $localProductCode = null,
        public ?bool $isDTPRequested = null,
        public ?bool $isInsuranceRequested = null,
        public array $charges = [],
        public ?ShipmentPurpose $shipmentPurpose = null,
        public ?TransportationMode $transportationMode = null,
        public ?MerchantCarrier $merchantSelectedCarrierName = null,
        public ?bool $getTariffFormula = null,
        public ?bool $getQuotationID = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'customerDetails' => $this->customerDetails->toArray(),
            'accounts' => array_map(static fn (Account $a): array => $a->toArray(), $this->accounts),
            'unitOfMeasurement' => $this->unitOfMeasurement->value,
            'currencyCode' => $this->currencyCode->value,
            'isCustomsDeclarable' => $this->isCustomsDeclarable,
            'getCostBreakdown' => $this->getCostBreakdown,
            'packages' => array_map(static fn (RatePackage $p): array => $p->toArray(), $this->packages),
            'items' => array_map(static fn (LineItem $i): array => $i->toArray(), $this->items),
        ];

        if ($this->productCode !== null) {
            $payload['productCode'] = $this->productCode;
        }
        if ($this->localProductCode !== null) {
            $payload['localProductCode'] = $this->localProductCode;
        }
        if ($this->isDTPRequested !== null) {
            $payload['isDTPRequested'] = $this->isDTPRequested;
        }
        if ($this->isInsuranceRequested !== null) {
            $payload['isInsuranceRequested'] = $this->isInsuranceRequested;
        }
        if ($this->charges !== []) {
            $payload['charges'] = array_map(static fn (Charge $c): array => $c->toArray(), $this->charges);
        }
        if ($this->shipmentPurpose !== null) {
            $payload['shipmentPurpose'] = $this->shipmentPurpose->value;
        }
        if ($this->transportationMode !== null) {
            $payload['transportationMode'] = $this->transportationMode->value;
        }
        if ($this->merchantSelectedCarrierName !== null) {
            $payload['merchantSelectedCarrierName'] = $this->merchantSelectedCarrierName->value;
        }
        if ($this->getTariffFormula !== null) {
            $payload['getTariffFormula'] = $this->getTariffFormula;
        }
        if ($this->getQuotationID !== null) {
            $payload['getQuotationID'] = $this->getQuotationID;
        }

        return $payload;
    }
}

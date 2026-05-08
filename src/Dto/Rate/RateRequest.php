<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\CustomerDetails;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Enum\RateProductTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\ValueObject\CountryCode;

/**
 * Top-level POST `/rates` request body.
 *
 * Mirrors `supermodelIoLogisticsExpressRateRequest`. The DHL spec
 * stamps `customerDetails`, `plannedShippingDateAndTime`,
 * `unitOfMeasurement`, `isCustomsDeclarable`, and `packages` as
 * required — those are non-optional constructor parameters.
 *
 * Construct one of these directly, or — for cross-field validation —
 * via {@see \Medzuch\DhlExpress\Builder\RateRequestBuilder}.
 */
final readonly class RateRequest
{
    /**
     * @param list<Account>                    $accounts
     * @param list<ValueAddedServiceFilter>    $valueAddedServices
     * @param list<ProductsAndServicesFilter>  $productsAndServices
     * @param list<MonetaryAmount>             $monetaryAmounts
     * @param list<AdditionalInformationOption> $getAdditionalInformation
     * @param list<RatePackage>                $packages
     */
    public function __construct(
        public CustomerDetails $customerDetails,
        public DateTimeImmutable $plannedShippingDateAndTime,
        public UnitSystem $unitOfMeasurement,
        public bool $isCustomsDeclarable,
        public array $packages,
        public array $accounts = [],
        public ?string $productCode = null,
        public ?string $localProductCode = null,
        public array $valueAddedServices = [],
        public array $productsAndServices = [],
        public ?CountryCode $payerCountryCode = null,
        public array $monetaryAmounts = [],
        public ?bool $requestAllValueAddedServices = null,
        public ?EstimatedDeliveryDateOption $estimatedDeliveryDate = null,
        public array $getAdditionalInformation = [],
        public ?bool $returnStandardProductsOnly = null,
        public ?bool $nextBusinessDay = null,
        public ?RateProductTypeCode $productTypeCode = null,
    ) {
    }

    /**
     * Serialize the request to the DHL JSON wire shape.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'customerDetails' => $this->customerDetails->toArray(),
            'plannedShippingDateAndTime' => $this->plannedShippingDateAndTime->format('Y-m-d\TH:i:s\G\M\TP'),
            'unitOfMeasurement' => $this->unitOfMeasurement->value,
            'isCustomsDeclarable' => $this->isCustomsDeclarable,
            'packages' => array_map(
                static fn (RatePackage $package): array => $package->toArray(),
                $this->packages,
            ),
        ];

        if ($this->accounts !== []) {
            $payload['accounts'] = array_map(
                static fn (Account $account): array => $account->toArray(),
                $this->accounts,
            );
        }
        if ($this->productCode !== null) {
            $payload['productCode'] = $this->productCode;
        }
        if ($this->localProductCode !== null) {
            $payload['localProductCode'] = $this->localProductCode;
        }
        if ($this->valueAddedServices !== []) {
            $payload['valueAddedServices'] = array_map(
                static fn (ValueAddedServiceFilter $filter): array => $filter->toArray(),
                $this->valueAddedServices,
            );
        }
        if ($this->productsAndServices !== []) {
            $payload['productsAndServices'] = array_map(
                static fn (ProductsAndServicesFilter $filter): array => $filter->toArray(),
                $this->productsAndServices,
            );
        }
        if ($this->payerCountryCode !== null) {
            $payload['payerCountryCode'] = $this->payerCountryCode->value;
        }
        if ($this->monetaryAmounts !== []) {
            $payload['monetaryAmount'] = array_map(
                static fn (MonetaryAmount $amount): array => $amount->toArray(),
                $this->monetaryAmounts,
            );
        }
        if ($this->requestAllValueAddedServices !== null) {
            $payload['requestAllValueAddedServices'] = $this->requestAllValueAddedServices;
        }
        if ($this->estimatedDeliveryDate !== null) {
            $payload['estimatedDeliveryDate'] = $this->estimatedDeliveryDate->toArray();
        }
        if ($this->getAdditionalInformation !== []) {
            $payload['getAdditionalInformation'] = array_map(
                static fn (AdditionalInformationOption $option): array => $option->toArray(),
                $this->getAdditionalInformation,
            );
        }
        if ($this->returnStandardProductsOnly !== null) {
            $payload['returnStandardProductsOnly'] = $this->returnStandardProductsOnly;
        }
        if ($this->nextBusinessDay !== null) {
            $payload['nextBusinessDay'] = $this->nextBusinessDay;
        }
        if ($this->productTypeCode !== null) {
            $payload['productTypeCode'] = $this->productTypeCode->value;
        }

        return $payload;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Builder;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\CustomerDetails;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Dto\Rate\AdditionalInformationOption;
use Medzuch\DhlExpress\Dto\Rate\EstimatedDeliveryDateOption;
use Medzuch\DhlExpress\Dto\Rate\MonetaryAmount;
use Medzuch\DhlExpress\Dto\Rate\ProductsAndServicesFilter;
use Medzuch\DhlExpress\Dto\Rate\RateRequest;
use Medzuch\DhlExpress\Dto\Rate\ValueAddedServiceFilter;
use Medzuch\DhlExpress\Enum\RateProductTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\CountryCode;

/**
 * Fluent builder for {@see RateRequest}.
 *
 * The first builder in the library — embodies the §7 validation
 * strategy. Constructor-level validation (VOs and DTOs) has already
 * happened by the time setters run; the builder only enforces
 * cross-field rules:
 *
 * - shipper, receiver, plannedShippingDate, unitOfMeasurement,
 *   isCustomsDeclarable, and ≥1 package must all be set.
 * - Every package's weight unit and dimension unit must align with
 *   the request-level `unitOfMeasurement`. Mixing metric and imperial
 *   inside a single shipment is the canonical example of a rule
 *   DHL would reject server-side; we surface it locally so callers
 *   get a single, structured error instead of a server 400.
 *
 * Errors accumulate during {@see self::build()} and surface
 * together as a single {@see InvalidRequestException}.
 */
final class RateRequestBuilder
{
    private ?RateAddress $shipper = null;
    private ?RateAddress $receiver = null;
    private ?DateTimeImmutable $plannedShippingDateAndTime = null;
    private ?UnitSystem $unitOfMeasurement = null;
    private ?bool $isCustomsDeclarable = null;
    /** @var list<RatePackage> */
    private array $packages = [];
    /** @var list<Account> */
    private array $accounts = [];
    private ?string $productCode = null;
    private ?string $localProductCode = null;
    /** @var list<ValueAddedServiceFilter> */
    private array $valueAddedServices = [];
    /** @var list<ProductsAndServicesFilter> */
    private array $productsAndServices = [];
    private ?CountryCode $payerCountryCode = null;
    /** @var list<MonetaryAmount> */
    private array $monetaryAmounts = [];
    private ?bool $requestAllValueAddedServices = null;
    private ?EstimatedDeliveryDateOption $estimatedDeliveryDate = null;
    /** @var list<AdditionalInformationOption> */
    private array $getAdditionalInformation = [];
    private ?bool $returnStandardProductsOnly = null;
    private ?bool $nextBusinessDay = null;
    private ?RateProductTypeCode $productTypeCode = null;

    public function withShipper(RateAddress $address): self
    {
        $this->shipper = $address;

        return $this;
    }

    public function withReceiver(RateAddress $address): self
    {
        $this->receiver = $address;

        return $this;
    }

    public function withPlannedShippingDate(DateTimeImmutable $dateTime): self
    {
        $this->plannedShippingDateAndTime = $dateTime;

        return $this;
    }

    public function withUnitSystem(UnitSystem $unitSystem): self
    {
        $this->unitOfMeasurement = $unitSystem;

        return $this;
    }

    public function withIsCustomsDeclarable(bool $isCustomsDeclarable): self
    {
        $this->isCustomsDeclarable = $isCustomsDeclarable;

        return $this;
    }

    public function withPackage(RatePackage $package): self
    {
        $this->packages[] = $package;

        return $this;
    }

    public function withAccount(Account $account): self
    {
        $this->accounts[] = $account;

        return $this;
    }

    public function withProductCode(string $productCode, ?string $localProductCode = null): self
    {
        $this->productCode = $productCode;
        $this->localProductCode = $localProductCode;

        return $this;
    }

    public function withValueAddedService(ValueAddedServiceFilter $filter): self
    {
        $this->valueAddedServices[] = $filter;

        return $this;
    }

    public function withProductsAndServicesFilter(ProductsAndServicesFilter $filter): self
    {
        $this->productsAndServices[] = $filter;

        return $this;
    }

    public function withPayerCountryCode(CountryCode $countryCode): self
    {
        $this->payerCountryCode = $countryCode;

        return $this;
    }

    public function withMonetaryAmount(MonetaryAmount $amount): self
    {
        $this->monetaryAmounts[] = $amount;

        return $this;
    }

    public function withRequestAllValueAddedServices(bool $flag): self
    {
        $this->requestAllValueAddedServices = $flag;

        return $this;
    }

    public function withEstimatedDeliveryDate(EstimatedDeliveryDateOption $option): self
    {
        $this->estimatedDeliveryDate = $option;

        return $this;
    }

    public function withAdditionalInformation(AdditionalInformationOption $option): self
    {
        $this->getAdditionalInformation[] = $option;

        return $this;
    }

    public function withReturnStandardProductsOnly(bool $flag): self
    {
        $this->returnStandardProductsOnly = $flag;

        return $this;
    }

    public function withNextBusinessDay(bool $flag): self
    {
        $this->nextBusinessDay = $flag;

        return $this;
    }

    public function withProductTypeCode(RateProductTypeCode $code): self
    {
        $this->productTypeCode = $code;

        return $this;
    }

    /**
     * @throws InvalidRequestException when one or more cross-field rules fail
     */
    public function build(): RateRequest
    {
        $shipper = $this->shipper;
        $receiver = $this->receiver;
        $plannedShippingDateAndTime = $this->plannedShippingDateAndTime;
        $unitOfMeasurement = $this->unitOfMeasurement;
        $isCustomsDeclarable = $this->isCustomsDeclarable;

        $errors = [];

        if ($shipper === null) {
            $errors[] = ['field' => 'shipper', 'message' => 'shipper is required'];
        }
        if ($receiver === null) {
            $errors[] = ['field' => 'receiver', 'message' => 'receiver is required'];
        }
        if ($plannedShippingDateAndTime === null) {
            $errors[] = ['field' => 'plannedShippingDateAndTime', 'message' => 'plannedShippingDateAndTime is required'];
        }
        if ($unitOfMeasurement === null) {
            $errors[] = ['field' => 'unitOfMeasurement', 'message' => 'unitOfMeasurement is required'];
        }
        if ($isCustomsDeclarable === null) {
            $errors[] = ['field' => 'isCustomsDeclarable', 'message' => 'isCustomsDeclarable is required'];
        }
        if ($this->packages === []) {
            $errors[] = ['field' => 'packages', 'message' => 'at least one package is required'];
        }

        if ($unitOfMeasurement !== null) {
            foreach ($this->packages as $index => $package) {
                if ($package->weight->system() !== $unitOfMeasurement) {
                    $errors[] = [
                        'field' => "packages[{$index}].weight.unit",
                        'message' => sprintf(
                            'package weight unit (%s) does not match shipment unitOfMeasurement (%s)',
                            $package->weight->unit->value,
                            $unitOfMeasurement->value,
                        ),
                    ];
                }
                if ($package->dimensions !== null && $package->dimensions->system() !== $unitOfMeasurement) {
                    $errors[] = [
                        'field' => "packages[{$index}].dimensions.unit",
                        'message' => sprintf(
                            'package dimension unit (%s) does not match shipment unitOfMeasurement (%s)',
                            $package->dimensions->unit->value,
                            $unitOfMeasurement->value,
                        ),
                    ];
                }
            }
        }

        if ($errors !== []) {
            throw new InvalidRequestException($errors);
        }

        // PHPStan narrowing: every field below has been proven non-null by the
        // validation block above; we restate the locals so the type-checker
        // tracks them through the constructor call.
        if ($receiver === null || $plannedShippingDateAndTime === null || $unitOfMeasurement === null || $isCustomsDeclarable === null) {
            throw new InvalidRequestException([
                ['field' => 'build', 'message' => 'internal: required field missing after validation'],
            ]);
        }

        return new RateRequest(
            customerDetails: new CustomerDetails($shipper, $receiver),
            plannedShippingDateAndTime: $plannedShippingDateAndTime,
            unitOfMeasurement: $unitOfMeasurement,
            isCustomsDeclarable: $isCustomsDeclarable,
            packages: $this->packages,
            accounts: $this->accounts,
            productCode: $this->productCode,
            localProductCode: $this->localProductCode,
            valueAddedServices: $this->valueAddedServices,
            productsAndServices: $this->productsAndServices,
            payerCountryCode: $this->payerCountryCode,
            monetaryAmounts: $this->monetaryAmounts,
            requestAllValueAddedServices: $this->requestAllValueAddedServices,
            estimatedDeliveryDate: $this->estimatedDeliveryDate,
            getAdditionalInformation: $this->getAdditionalInformation,
            returnStandardProductsOnly: $this->returnStandardProductsOnly,
            nextBusinessDay: $this->nextBusinessDay,
            productTypeCode: $this->productTypeCode,
        );
    }
}

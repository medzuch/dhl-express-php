<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Builder;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\Content;
use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentRequest;
use Medzuch\DhlExpress\Dto\Shipment\CustomerDetails;
use Medzuch\DhlExpress\Dto\Shipment\DangerousGoods;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\OutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Dto\Shipment\Pickup;
use Medzuch\DhlExpress\Dto\Shipment\ValueAddedService;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DangerousGoodsServiceCode;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Exception\InvalidRequestException;

/**
 * Fluent builder for {@see CreateShipmentRequest}.
 *
 * Second builder in the project (after
 * {@see RateRequestBuilder}). Embodies the §7 validation strategy:
 * constructor-level validation (VOs and DTOs) has already happened by
 * the time setters run; the builder enforces cross-field rules and
 * throws a single {@see InvalidRequestException} carrying every
 * issue.
 *
 * Phase 4a rules:
 * - All required fields present (shipper, receiver,
 *   plannedShippingDateAndTime, productCode, ≥1 account, ≥1 package,
 *   contentDescription, unitOfMeasurement, isCustomsDeclarable, and
 *   pickup.isRequested all explicitly set).
 * - Accounts: 1–3.
 * - Packages: 1–999.
 * - Per-package weight unit consistency: every package's weight unit
 *   must share the shipment-level {@see UnitSystem}. Mixing metric
 *   and imperial within a single shipment is the canonical example
 *   of a rule DHL would otherwise reject server-side.
 * - Per-package dimension unit consistency: same check on dimensions
 *   when present.
 *
 * Phase 4b rules (added):
 * - `isCustomsDeclarable=true` ⇒ `exportDeclaration` required.
 * - DG VAS code present ⇒ `dangerousGoods` block required.
 * - Insurance VAS (`II`) ⇒ `declaredValue` required.
 * - DDP incoterm ⇒ at least one `DutiesTaxes` account required.
 */
final class CreateShipmentBuilder
{
    private ?DateTimeImmutable $plannedShippingDateAndTime = null;
    private ?bool $pickupIsRequested = null;
    private ?string $productCode = null;
    private ?string $localProductCode = null;
    private ?ContactAddress $shipper = null;
    private ?ContactAddress $receiver = null;
    /** @var list<Account> */
    private array $accounts = [];
    /** @var list<Package> */
    private array $packages = [];
    private ?bool $isCustomsDeclarable = null;
    private ?string $contentDescription = null;
    private ?UnitSystem $unitOfMeasurement = null;
    private ?Incoterm $incoterm = null;
    /** @var list<ValueAddedService> */
    private array $valueAddedServices = [];
    private ?OutputImageProperties $outputImageProperties = null;
    private ?bool $getRateEstimates = null;
    private ?ExportDeclaration $exportDeclaration = null;
    private ?DangerousGoods $dangerousGoods = null;
    private ?float $declaredValue = null;
    private ?string $declaredValueCurrency = null;

    public function withPlannedShippingDate(DateTimeImmutable $dateTime): self
    {
        $this->plannedShippingDateAndTime = $dateTime;

        return $this;
    }

    public function withPickupRequested(bool $isRequested): self
    {
        $this->pickupIsRequested = $isRequested;

        return $this;
    }

    public function withProductCode(string $productCode, ?string $localProductCode = null): self
    {
        $this->productCode = $productCode;
        $this->localProductCode = $localProductCode;

        return $this;
    }

    public function withShipper(ContactAddress $address): self
    {
        $this->shipper = $address;

        return $this;
    }

    public function withReceiver(ContactAddress $address): self
    {
        $this->receiver = $address;

        return $this;
    }

    public function withAccount(Account $account): self
    {
        $this->accounts[] = $account;

        return $this;
    }

    public function withPackage(Package $package): self
    {
        $this->packages[] = $package;

        return $this;
    }

    public function withIsCustomsDeclarable(bool $isCustomsDeclarable): self
    {
        $this->isCustomsDeclarable = $isCustomsDeclarable;

        return $this;
    }

    public function withContentDescription(string $description): self
    {
        $this->contentDescription = $description;

        return $this;
    }

    public function withUnitSystem(UnitSystem $unitSystem): self
    {
        $this->unitOfMeasurement = $unitSystem;

        return $this;
    }

    public function withIncoterm(Incoterm $incoterm): self
    {
        $this->incoterm = $incoterm;

        return $this;
    }

    public function withValueAddedService(ValueAddedService $service): self
    {
        $this->valueAddedServices[] = $service;

        return $this;
    }

    public function withOutputImageProperties(OutputImageProperties $properties): self
    {
        $this->outputImageProperties = $properties;

        return $this;
    }

    public function withGetRateEstimates(bool $flag): self
    {
        $this->getRateEstimates = $flag;

        return $this;
    }

    public function withExportDeclaration(ExportDeclaration $declaration): self
    {
        $this->exportDeclaration = $declaration;

        return $this;
    }

    public function withDangerousGoods(DangerousGoods $dangerousGoods): self
    {
        $this->dangerousGoods = $dangerousGoods;

        return $this;
    }

    public function withDeclaredValue(float $value, string $currency): self
    {
        $this->declaredValue = $value;
        $this->declaredValueCurrency = $currency;

        return $this;
    }

    /**
     * @throws InvalidRequestException when one or more cross-field rules fail
     */
    public function build(): CreateShipmentRequest
    {
        $shipper = $this->shipper;
        $receiver = $this->receiver;
        $plannedShippingDateAndTime = $this->plannedShippingDateAndTime;
        $productCode = $this->productCode;
        $pickupIsRequested = $this->pickupIsRequested;
        $isCustomsDeclarable = $this->isCustomsDeclarable;
        $contentDescription = $this->contentDescription;
        $unitOfMeasurement = $this->unitOfMeasurement;

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
        if ($productCode === null) {
            $errors[] = ['field' => 'productCode', 'message' => 'productCode is required'];
        }
        if ($pickupIsRequested === null) {
            $errors[] = ['field' => 'pickup.isRequested', 'message' => 'pickup.isRequested is required'];
        }
        if ($isCustomsDeclarable === null) {
            $errors[] = ['field' => 'isCustomsDeclarable', 'message' => 'isCustomsDeclarable is required'];
        }
        if ($contentDescription === null) {
            $errors[] = ['field' => 'content.description', 'message' => 'content description is required'];
        }
        if ($unitOfMeasurement === null) {
            $errors[] = ['field' => 'unitOfMeasurement', 'message' => 'unitOfMeasurement is required'];
        }

        $accountCount = count($this->accounts);
        if ($accountCount < 1) {
            $errors[] = ['field' => 'accounts', 'message' => 'at least one account is required'];
        } elseif ($accountCount > 3) {
            $errors[] = [
                'field' => 'accounts',
                'message' => sprintf('at most 3 accounts allowed; got %d', $accountCount),
            ];
        }

        $packageCount = count($this->packages);
        if ($packageCount < 1) {
            $errors[] = ['field' => 'packages', 'message' => 'at least one package is required'];
        } elseif ($packageCount > 999) {
            $errors[] = [
                'field' => 'packages',
                'message' => sprintf('at most 999 packages allowed; got %d', $packageCount),
            ];
        }

        if ($isCustomsDeclarable === true && $this->exportDeclaration === null) {
            $errors[] = [
                'field' => 'content.exportDeclaration',
                'message' => 'exportDeclaration is required when isCustomsDeclarable is true',
            ];
        }

        // DG VAS rule: if any VAS has a DG service code, dangerousGoods block is required
        $hasDgVas = false;
        foreach ($this->valueAddedServices as $service) {
            if (DangerousGoodsServiceCode::tryFrom($service->serviceCode) !== null) {
                $hasDgVas = true;
                break;
            }
        }
        if ($hasDgVas && $this->dangerousGoods === null) {
            $errors[] = [
                'field' => 'dangerousGoods',
                'message' => 'dangerousGoods block is required when a dangerous-goods VAS service code is present',
            ];
        }

        // Insurance VAS rule: if VAS 'II' is present, declaredValue is required
        $hasInsuranceVas = false;
        foreach ($this->valueAddedServices as $service) {
            if ($service->serviceCode === 'II') {
                $hasInsuranceVas = true;
                break;
            }
        }
        if ($hasInsuranceVas && $this->declaredValue === null) {
            $errors[] = [
                'field' => 'content.declaredValue',
                'message' => 'declaredValue is required when insurance VAS (II) is present',
            ];
        }

        // DDP incoterm rule: at least one DutiesTaxes account required
        if ($this->incoterm === Incoterm::DDP) {
            $hasDutiesTaxesAccount = false;
            foreach ($this->accounts as $account) {
                if ($account->typeCode === AccountTypeCode::DutiesTaxes) {
                    $hasDutiesTaxesAccount = true;
                    break;
                }
            }
            if (!$hasDutiesTaxesAccount) {
                $errors[] = [
                    'field' => 'accounts',
                    'message' => 'a duties-taxes account is required when incoterm is DDP',
                ];
            }
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

        // PHPStan narrowing — the validation block above proves these are non-null
        // (it narrows $shipper on its own through the error-flow analysis but not
        // these locals).
        if (
            $receiver === null
            || $plannedShippingDateAndTime === null
            || $productCode === null
            || $pickupIsRequested === null
            || $isCustomsDeclarable === null
            || $contentDescription === null
            || $unitOfMeasurement === null
        ) {
            throw new InvalidRequestException([
                ['field' => 'build', 'message' => 'internal: required field missing after validation'],
            ]);
        }

        return new CreateShipmentRequest(
            plannedShippingDateAndTime: $plannedShippingDateAndTime,
            pickup: new Pickup($pickupIsRequested),
            productCode: $productCode,
            accounts: $this->accounts,
            customerDetails: new CustomerDetails($shipper, $receiver),
            content: new Content(
                packages: $this->packages,
                isCustomsDeclarable: $isCustomsDeclarable,
                description: $contentDescription,
                unitOfMeasurement: $unitOfMeasurement,
                incoterm: $this->incoterm,
                declaredValue: $this->declaredValue,
                declaredValueCurrency: $this->declaredValueCurrency,
                exportDeclaration: $this->exportDeclaration,
            ),
            localProductCode: $this->localProductCode,
            valueAddedServices: $this->valueAddedServices,
            outputImageProperties: $this->outputImageProperties,
            getRateEstimates: $this->getRateEstimates,
            dangerousGoods: $this->dangerousGoods,
        );
    }
}

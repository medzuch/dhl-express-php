<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Builder;

use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\CustomerDetails;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Dto\LandedCost\Charge;
use Medzuch\DhlExpress\Dto\LandedCost\LandedCostRequest;
use Medzuch\DhlExpress\Dto\LandedCost\LineItem;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\MerchantCarrier;
use Medzuch\DhlExpress\Enum\ShipmentPurpose;
use Medzuch\DhlExpress\Enum\TransportationMode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;

/**
 * Fluent builder for {@see LandedCostRequest}.
 *
 * Same accumulate-and-throw model as {@see RateRequestBuilder};
 * adds three more cross-field rules on top of the rate-request
 * checks: a `currencyCode` must be set, at least one line item is
 * required, and at least one of the supplied accounts must be the
 * `shipper` account.
 */
final class LandedCostRequestBuilder
{
    private ?RateAddress $shipper = null;
    private ?RateAddress $receiver = null;
    /** @var list<Account> */
    private array $accounts = [];
    private ?UnitSystem $unitOfMeasurement = null;
    private ?CurrencyCode $currencyCode = null;
    private ?bool $isCustomsDeclarable = null;
    private bool $getCostBreakdown = true;
    /** @var list<RatePackage> */
    private array $packages = [];
    /** @var list<LineItem> */
    private array $items = [];
    private ?string $productCode = null;
    private ?string $localProductCode = null;
    private ?bool $isDTPRequested = null;
    private ?bool $isInsuranceRequested = null;
    /** @var list<Charge> */
    private array $charges = [];
    private ?ShipmentPurpose $shipmentPurpose = null;
    private ?TransportationMode $transportationMode = null;
    private ?MerchantCarrier $merchantSelectedCarrierName = null;
    private ?bool $getTariffFormula = null;
    private ?bool $getQuotationID = null;

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

    public function withAccount(Account $account): self
    {
        $this->accounts[] = $account;

        return $this;
    }

    public function withUnitSystem(UnitSystem $unitSystem): self
    {
        $this->unitOfMeasurement = $unitSystem;

        return $this;
    }

    public function withCurrency(CurrencyCode $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    public function withIsCustomsDeclarable(bool $isCustomsDeclarable): self
    {
        $this->isCustomsDeclarable = $isCustomsDeclarable;

        return $this;
    }

    public function withGetCostBreakdown(bool $getCostBreakdown): self
    {
        $this->getCostBreakdown = $getCostBreakdown;

        return $this;
    }

    public function withPackage(RatePackage $package): self
    {
        $this->packages[] = $package;

        return $this;
    }

    public function withLineItem(LineItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function withProductCode(string $productCode, ?string $localProductCode = null): self
    {
        $this->productCode = $productCode;
        $this->localProductCode = $localProductCode;

        return $this;
    }

    public function withDTPRequested(bool $flag): self
    {
        $this->isDTPRequested = $flag;

        return $this;
    }

    public function withInsuranceRequested(bool $flag): self
    {
        $this->isInsuranceRequested = $flag;

        return $this;
    }

    public function withCharge(Charge $charge): self
    {
        $this->charges[] = $charge;

        return $this;
    }

    public function withShipmentPurpose(ShipmentPurpose $purpose): self
    {
        $this->shipmentPurpose = $purpose;

        return $this;
    }

    public function withTransportationMode(TransportationMode $mode): self
    {
        $this->transportationMode = $mode;

        return $this;
    }

    public function withMerchantCarrier(MerchantCarrier $carrier): self
    {
        $this->merchantSelectedCarrierName = $carrier;

        return $this;
    }

    public function withTariffFormula(bool $flag): self
    {
        $this->getTariffFormula = $flag;

        return $this;
    }

    public function withQuotationId(bool $flag): self
    {
        $this->getQuotationID = $flag;

        return $this;
    }

    /**
     * @throws InvalidRequestException when one or more cross-field rules fail
     */
    public function build(): LandedCostRequest
    {
        $shipper = $this->shipper;
        $receiver = $this->receiver;
        $unitOfMeasurement = $this->unitOfMeasurement;
        $currencyCode = $this->currencyCode;
        $isCustomsDeclarable = $this->isCustomsDeclarable;

        $errors = [];

        if ($shipper === null) {
            $errors[] = ['field' => 'shipper', 'message' => 'shipper is required'];
        }
        if ($receiver === null) {
            $errors[] = ['field' => 'receiver', 'message' => 'receiver is required'];
        }
        if ($unitOfMeasurement === null) {
            $errors[] = ['field' => 'unitOfMeasurement', 'message' => 'unitOfMeasurement is required'];
        }
        if ($currencyCode === null) {
            $errors[] = ['field' => 'currencyCode', 'message' => 'currencyCode is required'];
        }
        if ($isCustomsDeclarable === null) {
            $errors[] = ['field' => 'isCustomsDeclarable', 'message' => 'isCustomsDeclarable is required'];
        }
        if ($this->packages === []) {
            $errors[] = ['field' => 'packages', 'message' => 'at least one package is required'];
        }
        if ($this->items === []) {
            $errors[] = ['field' => 'items', 'message' => 'at least one line item is required'];
        }
        if ($this->accounts === []) {
            $errors[] = ['field' => 'accounts', 'message' => 'at least one account is required'];
        } else {
            $hasShipperAccount = false;
            foreach ($this->accounts as $account) {
                if ($account->typeCode === AccountTypeCode::Shipper) {
                    $hasShipperAccount = true;
                    break;
                }
            }
            if (!$hasShipperAccount) {
                $errors[] = ['field' => 'accounts', 'message' => 'at least one account with typeCode "shipper" is required'];
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

        if ($receiver === null || $unitOfMeasurement === null || $currencyCode === null || $isCustomsDeclarable === null) {
            throw new InvalidRequestException([
                ['field' => 'build', 'message' => 'internal: required field missing after validation'],
            ]);
        }

        return new LandedCostRequest(
            customerDetails: new CustomerDetails($shipper, $receiver),
            accounts: $this->accounts,
            unitOfMeasurement: $unitOfMeasurement,
            currencyCode: $currencyCode,
            isCustomsDeclarable: $isCustomsDeclarable,
            getCostBreakdown: $this->getCostBreakdown,
            packages: $this->packages,
            items: $this->items,
            productCode: $this->productCode,
            localProductCode: $this->localProductCode,
            isDTPRequested: $this->isDTPRequested,
            isInsuranceRequested: $this->isInsuranceRequested,
            charges: $this->charges,
            shipmentPurpose: $this->shipmentPurpose,
            transportationMode: $this->transportationMode,
            merchantSelectedCarrierName: $this->merchantSelectedCarrierName,
            getTariffFormula: $this->getTariffFormula,
            getQuotationID: $this->getQuotationID,
        );
    }
}

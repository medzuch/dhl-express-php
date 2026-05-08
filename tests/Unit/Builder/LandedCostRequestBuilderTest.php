<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Builder;

use Medzuch\DhlExpress\Builder\LandedCostRequestBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Dto\LandedCost\LineItem;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class LandedCostRequestBuilderTest extends TestCase
{
    public function testBuildsValidRequestFromFullInput(): void
    {
        $request = $this->minimalBuilder()->build();

        self::assertSame(UnitSystem::Metric, $request->unitOfMeasurement);
        self::assertSame('AUD', $request->currencyCode->value);
        self::assertCount(1, $request->packages);
        self::assertCount(1, $request->items);
    }

    public function testRequiresAtLeastOneShipperAccount(): void
    {
        $builder = $this->minimalBuilderWithoutAccount()
            ->withAccount(new Account(AccountTypeCode::Payer, new AccountNumber('999')));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $messages = array_column($exception->errors(), 'message');
            self::assertContains('at least one account with typeCode "shipper" is required', $messages);
        }
    }

    public function testRequiresAtLeastOneLineItem(): void
    {
        $builder = (new LandedCostRequestBuilder())
            ->withShipper(new RateAddress(new CountryCode('US'), new PostalCode('90011'), 'LOS ANGELES'))
            ->withReceiver(new RateAddress(new CountryCode('AU'), new PostalCode('1021'), 'MELBOURNE'))
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withUnitSystem(UnitSystem::Metric)
            ->withCurrency(new CurrencyCode('AUD'))
            ->withIsCustomsDeclarable(true)
            ->withPackage(new RatePackage(new Weight(1.0, WeightUnit::KG)));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('items', $fields);
        }
    }

    public function testRejectsMissingCurrency(): void
    {
        $builder = (new LandedCostRequestBuilder())
            ->withShipper(new RateAddress(new CountryCode('US'), new PostalCode('90011'), 'LOS ANGELES'))
            ->withReceiver(new RateAddress(new CountryCode('AU'), new PostalCode('1021'), 'MELBOURNE'))
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123')))
            ->withUnitSystem(UnitSystem::Metric)
            ->withIsCustomsDeclarable(true)
            ->withPackage(new RatePackage(new Weight(1.0, WeightUnit::KG)))
            ->withLineItem($this->sampleLineItem());

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('currencyCode', $fields);
        }
    }

    private function minimalBuilder(): LandedCostRequestBuilder
    {
        return $this->minimalBuilderWithoutAccount()
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')));
    }

    private function minimalBuilderWithoutAccount(): LandedCostRequestBuilder
    {
        return (new LandedCostRequestBuilder())
            ->withShipper(new RateAddress(new CountryCode('US'), new PostalCode('90011'), 'LOS ANGELES'))
            ->withReceiver(new RateAddress(new CountryCode('AU'), new PostalCode('1021'), 'MELBOURNE'))
            ->withUnitSystem(UnitSystem::Metric)
            ->withCurrency(new CurrencyCode('AUD'))
            ->withIsCustomsDeclarable(true)
            ->withPackage(new RatePackage(new Weight(1.0, WeightUnit::KG)))
            ->withLineItem($this->sampleLineItem());
    }

    private function sampleLineItem(): LineItem
    {
        return new LineItem(
            number: 1,
            quantity: 2.0,
            unitPrice: 120.0,
            unitPriceCurrencyCode: new CurrencyCode('AUD'),
            manufacturerCountry: new CountryCode('CN'),
            name: 'KNITWEAR COTTON',
            commodityCode: '610910',
            weight: 1.5,
            weightUnitOfMeasurement: UnitSystem::Metric,
        );
    }
}

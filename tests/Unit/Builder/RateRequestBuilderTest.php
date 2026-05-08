<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Builder;

use DateTimeImmutable;
use Medzuch\DhlExpress\Builder\RateRequestBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class RateRequestBuilderTest extends TestCase
{
    public function testBuildsValidRequestFromMinimalInput(): void
    {
        $builder = $this->minimalMetricBuilder();

        $request = $builder->build();

        self::assertSame(UnitSystem::Metric, $request->unitOfMeasurement);
        self::assertCount(1, $request->packages);
        self::assertSame('Prague', $request->customerDetails->shipperDetails->cityName);
    }

    public function testAccumulatesAllMissingFieldsIntoOneException(): void
    {
        $builder = new RateRequestBuilder();

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');

            self::assertContains('shipper', $fields);
            self::assertContains('receiver', $fields);
            self::assertContains('plannedShippingDateAndTime', $fields);
            self::assertContains('unitOfMeasurement', $fields);
            self::assertContains('isCustomsDeclarable', $fields);
            self::assertContains('packages', $fields);
        }
    }

    public function testRejectsMixedUnitWeightAgainstMetricRequest(): void
    {
        $builder = $this->minimalMetricBuilder()
            ->withPackage(new RatePackage(new Weight(2.0, WeightUnit::LB)));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');

            self::assertContains('packages[1].weight.unit', $fields);
        }
    }

    public function testRejectsMixedUnitDimensionsAgainstMetricRequest(): void
    {
        $builder = (new RateRequestBuilder())
            ->withShipper(new RateAddress(new CountryCode('CZ'), new PostalCode('14800'), 'Prague'))
            ->withReceiver(new RateAddress(new CountryCode('DE'), new PostalCode('10115'), 'Berlin'))
            ->withPlannedShippingDate(new DateTimeImmutable('2026-06-01T13:00:00+00:00'))
            ->withUnitSystem(UnitSystem::Metric)
            ->withIsCustomsDeclarable(false)
            ->withPackage(new RatePackage(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(10.0, 10.0, 10.0, DimensionUnit::IN),
            ));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');

            self::assertContains('packages[0].dimensions.unit', $fields);
        }
    }

    public function testFluentSettersCarryAllOptionalFields(): void
    {
        $builder = $this->minimalMetricBuilder()
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withProductCode('P', 'P')
            ->withPayerCountryCode(new CountryCode('CZ'))
            ->withNextBusinessDay(true)
            ->withReturnStandardProductsOnly(false);

        $request = $builder->build();

        self::assertCount(1, $request->accounts);
        self::assertSame('P', $request->productCode);
        self::assertSame('CZ', $request->payerCountryCode?->value);
        self::assertTrue($request->nextBusinessDay);
        self::assertFalse($request->returnStandardProductsOnly);
    }

    private function minimalMetricBuilder(): RateRequestBuilder
    {
        return (new RateRequestBuilder())
            ->withShipper(new RateAddress(new CountryCode('CZ'), new PostalCode('14800'), 'Prague'))
            ->withReceiver(new RateAddress(new CountryCode('DE'), new PostalCode('10115'), 'Berlin'))
            ->withPlannedShippingDate(new DateTimeImmutable('2026-06-01T13:00:00+00:00'))
            ->withUnitSystem(UnitSystem::Metric)
            ->withIsCustomsDeclarable(false)
            ->withPackage(new RatePackage(new Weight(1.0, WeightUnit::KG)));
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Rate;

use Medzuch\DhlExpress\Builder\RateRequestBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Dto\Rate\RatesResponse;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\Attributes\Group;

/**
 * Sandbox checks for `/rates` (GET single-piece + POST multi-piece).
 *
 * Uses Prague→Berlin as a stable customs-free EU lane and a future
 * weekday via {@see IntegrationTestCase::nextBusinessDay()} so the
 * planned-shipping-date stays valid relative to whatever day the
 * suite runs on.
 */
#[Group('integration')]
final class RatesApiIntegrationTest extends IntegrationTestCase
{
    public function testQuoteSinglePieceReturnsProducts(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        $response = $client->rates()->quote(
            account: $account,
            originCountryCode: new CountryCode('CZ'),
            destinationCountryCode: new CountryCode('DE'),
            weight: new Weight(2.0, WeightUnit::KG),
            dimensions: new Dimensions(20.0, 20.0, 10.0, DimensionUnit::CM),
            plannedShippingDate: $this->nextBusinessDay(3),
            isCustomsDeclarable: false,
            unitOfMeasurement: UnitSystem::Metric,
            originPostalCode: new PostalCode('14800'),
            originCityName: 'Prague',
            destinationPostalCode: new PostalCode('10115'),
            destinationCityName: 'Berlin',
        );

        self::assertInstanceOf(RatesResponse::class, $response);
        self::assertNotSame([], $response->products);

        $first = $response->products[0];
        self::assertNotEmpty($first->productCode);
        self::assertNotEmpty($first->productName);
        self::assertNotSame([], $first->totalPrices);
    }

    public function testQuoteManyMultiPieceReturnsProducts(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        $request = (new RateRequestBuilder())
            ->withShipper(new RateAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('14800'),
                cityName: 'Prague',
            ))
            ->withReceiver(new RateAddress(
                countryCode: new CountryCode('DE'),
                postalCode: new PostalCode('10115'),
                cityName: 'Berlin',
            ))
            ->withAccount(new Account(AccountTypeCode::Shipper, $account))
            ->withPlannedShippingDate($this->nextBusinessDay(3)->setTime(13, 0, 0))
            ->withUnitSystem(UnitSystem::Metric)
            ->withIsCustomsDeclarable(false)
            ->withPackage(new RatePackage(
                weight: new Weight(2.0, WeightUnit::KG),
                dimensions: new Dimensions(20.0, 20.0, 10.0, DimensionUnit::CM),
            ))
            ->withPackage(new RatePackage(
                weight: new Weight(1.5, WeightUnit::KG),
                dimensions: new Dimensions(15.0, 15.0, 10.0, DimensionUnit::CM),
            ))
            ->build();

        $response = $client->rates()->quoteMany($request);

        self::assertInstanceOf(RatesResponse::class, $response);
        self::assertNotSame([], $response->products);

        $first = $response->products[0];
        self::assertNotEmpty($first->productCode);
        self::assertNotSame([], $first->totalPrices);
    }
}

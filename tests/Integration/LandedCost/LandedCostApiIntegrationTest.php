<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\LandedCost;

use Medzuch\DhlExpress\Builder\LandedCostRequestBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Dto\LandedCost\LineItem;
use Medzuch\DhlExpress\Dto\Rate\RatesResponse;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\Enum\ShipmentPurpose;
use Medzuch\DhlExpress\Enum\TransportationMode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\Attributes\Group;

/**
 * Sandbox checks for `POST /landed-cost`.
 *
 * Uses the OpenAPI example payload (US→AU shipment of cotton
 * knitwear) so we hit a customs-declarable lane that exercises the
 * landed-cost calculator's duty / tax / freight machinery.
 */
#[Group('integration')]
final class LandedCostApiIntegrationTest extends IntegrationTestCase
{
    public function testEstimateReturnsProducts(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        $request = (new LandedCostRequestBuilder())
            ->withShipper(new RateAddress(
                countryCode: new CountryCode('US'),
                postalCode: new PostalCode('90011'),
                cityName: 'LOS ANGELES',
            ))
            ->withReceiver(new RateAddress(
                countryCode: new CountryCode('AU'),
                postalCode: new PostalCode('1021'),
                cityName: 'MELBOURNE',
            ))
            ->withAccount(new Account(AccountTypeCode::Shipper, $account))
            ->withUnitSystem(UnitSystem::Metric)
            ->withCurrency(new CurrencyCode('AUD'))
            ->withIsCustomsDeclarable(true)
            ->withGetCostBreakdown(true)
            ->withPackage(new RatePackage(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(25.0, 35.0, 15.0, DimensionUnit::CM),
                typeCode: PackageTypeCode::Box3,
            ))
            ->withLineItem(new LineItem(
                number: 1,
                quantity: 2.0,
                unitPrice: 120.0,
                unitPriceCurrencyCode: new CurrencyCode('AUD'),
                manufacturerCountry: new CountryCode('CN'),
                name: 'KNITWEAR COTTON',
                description: 'KNITWEAR 100% COTTON',
                commodityCode: '610910',
                weight: 1.0,
                weightUnitOfMeasurement: UnitSystem::Metric,
            ))
            ->withProductCode('P', 'P')
            ->withShipmentPurpose(ShipmentPurpose::Personal)
            ->withTransportationMode(TransportationMode::Air)
            ->build();

        $response = $client->landedCost()->estimate($request);

        self::assertInstanceOf(RatesResponse::class, $response);
        self::assertNotSame([], $response->products);

        // The /landed-cost response usually omits productCode/productName
        // and instead carries the cost breakdown via totalPrice plus the
        // per-line-item charges in the `items` block (raw under
        // QuotedProduct::$rawItems).
        $first = $response->products[0];
        self::assertNotSame([], $first->totalPrices);
        self::assertNotSame([], $first->rawItems);
    }
}

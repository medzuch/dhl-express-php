<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\LandedCost;

use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Common\CustomerDetails;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Dto\LandedCost\Charge;
use Medzuch\DhlExpress\Dto\LandedCost\LandedCostRequest;
use Medzuch\DhlExpress\Dto\LandedCost\LineItem;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\ChargeTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\MerchantCarrier;
use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\Enum\ShipmentPurpose;
use Medzuch\DhlExpress\Enum\TransportationMode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class LandedCostRequestTest extends TestCase
{
    public function testToArrayMatchesOpenApiExamplePayload(): void
    {
        $request = new LandedCostRequest(
            customerDetails: new CustomerDetails(
                shipperDetails: new RateAddress(
                    countryCode: new CountryCode('US'),
                    postalCode: new PostalCode('90011'),
                    cityName: 'LOS ANGELES',
                ),
                receiverDetails: new RateAddress(
                    countryCode: new CountryCode('AU'),
                    postalCode: new PostalCode('1021'),
                    cityName: 'MELBOURNE',
                ),
            ),
            accounts: [
                new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')),
            ],
            unitOfMeasurement: UnitSystem::Metric,
            currencyCode: new CurrencyCode('AUD'),
            isCustomsDeclarable: true,
            getCostBreakdown: true,
            packages: [
                new RatePackage(
                    weight: new Weight(1.0, WeightUnit::KG),
                    dimensions: new Dimensions(25.0, 35.0, 15.0, DimensionUnit::CM),
                    typeCode: PackageTypeCode::Box3,
                ),
            ],
            items: [
                new LineItem(
                    number: 1,
                    quantity: 2.0,
                    unitPrice: 120.0,
                    unitPriceCurrencyCode: new CurrencyCode('AUD'),
                    manufacturerCountry: new CountryCode('CN'),
                    name: 'KNITWEAR COTTON',
                ),
            ],
            productCode: 'P',
            localProductCode: 'P',
            isDTPRequested: true,
            isInsuranceRequested: false,
            charges: [
                new Charge(ChargeTypeCode::Insurance, 10.0, new CurrencyCode('AUD')),
            ],
            shipmentPurpose: ShipmentPurpose::Personal,
            transportationMode: TransportationMode::Air,
            merchantSelectedCarrierName: MerchantCarrier::DHL,
        );

        $payload = $request->toArray();

        self::assertSame('LOS ANGELES', $payload['customerDetails']['shipperDetails']['cityName']);
        self::assertSame('MELBOURNE', $payload['customerDetails']['receiverDetails']['cityName']);
        self::assertSame([['typeCode' => 'shipper', 'number' => '123456789']], $payload['accounts']);
        self::assertSame('P', $payload['productCode']);
        self::assertSame('AUD', $payload['currencyCode']);
        self::assertTrue($payload['isCustomsDeclarable']);
        self::assertTrue($payload['isDTPRequested']);
        self::assertFalse($payload['isInsuranceRequested']);
        self::assertTrue($payload['getCostBreakdown']);
        self::assertSame(
            [['typeCode' => 'insurance', 'amount' => 10.0, 'currencyCode' => 'AUD']],
            $payload['charges'],
        );
        self::assertSame('personal', $payload['shipmentPurpose']);
        self::assertSame('air', $payload['transportationMode']);
        self::assertSame('DHL', $payload['merchantSelectedCarrierName']);
        self::assertCount(1, $payload['packages']);
        self::assertCount(1, $payload['items']);
        self::assertSame('CN', $payload['items'][0]['manufacturerCountry']);
    }
}

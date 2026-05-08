<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Rate;

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
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\AdditionalInformationTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateTypeCode;
use Medzuch\DhlExpress\Enum\MonetaryAmountTypeCode;
use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\Enum\RateProductTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class RateRequestTest extends TestCase
{
    public function testMinimalRequestEmitsRequiredFieldsOnly(): void
    {
        $request = new RateRequest(
            customerDetails: $this->minimalCustomerDetails(),
            plannedShippingDateAndTime: new DateTimeImmutable('2022-11-20T13:00:00+00:00'),
            unitOfMeasurement: UnitSystem::Metric,
            isCustomsDeclarable: false,
            packages: [new RatePackage(new Weight(1.0, WeightUnit::KG))],
        );

        $payload = $request->toArray();

        self::assertSame('2022-11-20T13:00:00GMT+00:00', $payload['plannedShippingDateAndTime']);
        self::assertSame('metric', $payload['unitOfMeasurement']);
        self::assertFalse($payload['isCustomsDeclarable']);
        self::assertCount(1, $payload['packages']);
        self::assertArrayNotHasKey('accounts', $payload);
        self::assertArrayNotHasKey('productCode', $payload);
        self::assertArrayNotHasKey('estimatedDeliveryDate', $payload);
    }

    public function testToArrayMatchesOpenApiExamplePayload(): void
    {
        $request = new RateRequest(
            customerDetails: new CustomerDetails(
                shipperDetails: new RateAddress(
                    countryCode: new CountryCode('SG'),
                    postalCode: new PostalCode('048582'),
                    cityName: 'SINGAPORE',
                    addressLine1: 'Blk 6 Lock Rd',
                    addressLine2: '02-10 Gillman Barracks',
                    addressLine3: 'Barrack Street',
                ),
                receiverDetails: new RateAddress(
                    countryCode: new CountryCode('FR'),
                    postalCode: new PostalCode('75001'),
                    cityName: 'PARIS',
                    addressLine1: '9',
                    addressLine2: 'Rue Simart',
                ),
            ),
            plannedShippingDateAndTime: new DateTimeImmutable('2022-11-20T13:00:00+00:00'),
            unitOfMeasurement: UnitSystem::Metric,
            isCustomsDeclarable: true,
            packages: [
                new RatePackage(
                    weight: new Weight(1.0, WeightUnit::KG),
                    dimensions: new Dimensions(10.0, 20.0, 30.0, DimensionUnit::CM),
                    typeCode: PackageTypeCode::Box3,
                ),
            ],
            accounts: [
                new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')),
            ],
            productsAndServices: [
                new ProductsAndServicesFilter(
                    productCode: 'P',
                    localProductCode: 'P',
                ),
            ],
            payerCountryCode: new CountryCode('SG'),
            monetaryAmounts: [
                new MonetaryAmount(MonetaryAmountTypeCode::DeclaredValue, 100.0, new CurrencyCode('SGD')),
            ],
            estimatedDeliveryDate: new EstimatedDeliveryDateOption(
                isRequested: true,
                typeCode: EstimatedDeliveryDateTypeCode::QDDC,
            ),
            getAdditionalInformation: [
                new AdditionalInformationOption(
                    AdditionalInformationTypeCode::AllValueAddedServices,
                    true,
                ),
            ],
            returnStandardProductsOnly: false,
            nextBusinessDay: true,
            productTypeCode: RateProductTypeCode::All,
        );

        $payload = $request->toArray();

        self::assertSame('SINGAPORE', $payload['customerDetails']['shipperDetails']['cityName']);
        self::assertSame('PARIS', $payload['customerDetails']['receiverDetails']['cityName']);
        self::assertSame([['typeCode' => 'shipper', 'number' => '123456789']], $payload['accounts']);
        self::assertSame(
            [['productCode' => 'P', 'localProductCode' => 'P']],
            $payload['productsAndServices'],
        );
        self::assertSame('SG', $payload['payerCountryCode']);
        self::assertSame(
            [['typeCode' => 'declaredValue', 'value' => 100.0, 'currency' => 'SGD']],
            $payload['monetaryAmount'],
        );
        self::assertSame(
            ['isRequested' => true, 'typeCode' => 'QDDC'],
            $payload['estimatedDeliveryDate'],
        );
        self::assertSame(
            [['typeCode' => 'allValueAddedServices', 'isRequested' => true]],
            $payload['getAdditionalInformation'],
        );
        self::assertFalse($payload['returnStandardProductsOnly']);
        self::assertTrue($payload['nextBusinessDay']);
        self::assertSame('all', $payload['productTypeCode']);
        self::assertSame(
            [
                'weight' => 1.0,
                'typeCode' => '3BX',
                'dimensions' => ['length' => 10.0, 'width' => 20.0, 'height' => 30.0],
            ],
            $payload['packages'][0],
        );
    }

    private function minimalCustomerDetails(): CustomerDetails
    {
        return new CustomerDetails(
            shipperDetails: new RateAddress(new CountryCode('CZ'), new PostalCode('14800'), 'Prague'),
            receiverDetails: new RateAddress(new CountryCode('DE'), new PostalCode('10115'), 'Berlin'),
        );
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Builder;

use DateTimeImmutable;
use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
use Medzuch\DhlExpress\Dto\Shipment\OutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Dto\Shipment\ValueAddedService;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class CreateShipmentBuilderTest extends TestCase
{
    public function testBuildsValidRequestFromMinimalInput(): void
    {
        $request = $this->minimalDomesticBuilder()->build();

        self::assertSame('N', $request->productCode);
        self::assertCount(1, $request->accounts);
        self::assertCount(1, $request->content->packages);
        self::assertFalse($request->content->isCustomsDeclarable);
        self::assertFalse($request->pickup->isRequested);
        self::assertSame('Prague', $request->customerDetails->shipperDetails->cityName);
        self::assertSame('Brno', $request->customerDetails->receiverDetails->cityName);
    }

    public function testAccumulatesAllMissingFieldsIntoOneException(): void
    {
        $builder = new CreateShipmentBuilder();

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');

            self::assertContains('shipper', $fields);
            self::assertContains('receiver', $fields);
            self::assertContains('plannedShippingDateAndTime', $fields);
            self::assertContains('productCode', $fields);
            self::assertContains('pickup.isRequested', $fields);
            self::assertContains('isCustomsDeclarable', $fields);
            self::assertContains('content.description', $fields);
            self::assertContains('unitOfMeasurement', $fields);
            self::assertContains('accounts', $fields);
            self::assertContains('packages', $fields);
        }
    }

    public function testRejectsCustomsDeclarableInPhase4a(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(true);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');

            self::assertContains('isCustomsDeclarable', $fields);
        }
    }

    public function testRejectsMixedWeightUnitsAcrossPackages(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withPackage(new Package(new Weight(2.0, WeightUnit::LB)));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');

            self::assertContains('packages[1].weight.unit', $fields);
        }
    }

    public function testRejectsMixedDimensionUnitsAcrossPackages(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withPackage(new Package(
                weight: new Weight(2.0, WeightUnit::KG),
                dimensions: new Dimensions(10.0, 10.0, 10.0, DimensionUnit::IN),
            ));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');

            self::assertContains('packages[1].dimensions.unit', $fields);
        }
    }

    public function testRejectsMoreThanThreeAccounts(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withAccount(new Account(AccountTypeCode::Payer, new AccountNumber('111111111')))
            ->withAccount(new Account(AccountTypeCode::DutiesTaxes, new AccountNumber('222222222')))
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('333333333')));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');

            self::assertContains('accounts', $fields);
        }
    }

    public function testFluentSettersCarryAllOptionalFields(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withProductCode('N', 'N')
            ->withValueAddedService(new ValueAddedService('II', value: 50.0))
            ->withOutputImageProperties(new OutputImageProperties(
                encodingFormat: LabelEncodingFormat::Pdf,
                imageOptions: [new ImageOption(typeCode: 'label', isRequested: true)],
            ))
            ->withGetRateEstimates(true);

        $request = $builder->build();

        self::assertSame('N', $request->localProductCode);
        self::assertSame('Books', $request->content->description);
        self::assertCount(1, $request->valueAddedServices);
        self::assertNotNull($request->outputImageProperties);
        self::assertSame(LabelEncodingFormat::Pdf, $request->outputImageProperties->encodingFormat);
        self::assertTrue($request->getRateEstimates);
    }

    public function testToArrayProducesExpectedTopLevelShape(): void
    {
        $payload = $this->minimalDomesticBuilder()->build()->toArray();

        self::assertArrayHasKey('plannedShippingDateAndTime', $payload);
        self::assertArrayHasKey('pickup', $payload);
        self::assertArrayHasKey('productCode', $payload);
        self::assertArrayHasKey('accounts', $payload);
        self::assertArrayHasKey('customerDetails', $payload);
        self::assertArrayHasKey('content', $payload);
        self::assertSame(['isRequested' => false], $payload['pickup']);
        self::assertSame('N', $payload['productCode']);
    }

    private function minimalDomesticBuilder(): CreateShipmentBuilder
    {
        return (new CreateShipmentBuilder())
            ->withShipper($this->shipperContact())
            ->withReceiver($this->receiverContact())
            ->withPlannedShippingDate(new DateTimeImmutable('2026-06-01T13:00:00+00:00'))
            ->withProductCode('N')
            ->withPickupRequested(false)
            ->withIsCustomsDeclarable(false)
            ->withContentDescription('Books')
            ->withUnitSystem(UnitSystem::Metric)
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withPackage(new Package(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
            ));
    }

    private function shipperContact(): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
            addressLine1: 'Vaclavske namesti 1',
            phone: new PhoneNumber('+420 222 333 444'),
            companyName: 'Alfa Trading s.r.o.',
            fullName: 'Jan Nowak',
        );
    }

    private function receiverContact(): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('60200'),
            cityName: 'Brno',
            addressLine1: 'Namesti Svobody 10',
            phone: new PhoneNumber('+420 555 666 777'),
            companyName: 'Receiver s.r.o.',
            fullName: 'Receiver Name',
        );
    }
}

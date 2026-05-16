<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Builder;

use DateTimeImmutable;
use DateTimeZone;
use Medzuch\DhlExpress\Builder\PickupRequestBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupRequest;
use Medzuch\DhlExpress\Dto\Pickup\PickupPackage;
use Medzuch\DhlExpress\Dto\Pickup\PickupShipmentDetails;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class PickupRequestBuilderTest extends TestCase
{
    // ----- happy path -----

    public function testBuildReturnsCreatePickupRequestWhenAllRequiredFieldsSet(): void
    {
        $request = $this->minimalBuilder()->build();

        self::assertInstanceOf(CreatePickupRequest::class, $request);
    }

    public function testOptionalCloseTimeIsPassedThrough(): void
    {
        $request = $this->minimalBuilder()
            ->withCloseTime('18:00')
            ->build();

        self::assertSame('18:00', $request->closeTime);
    }

    public function testOptionalLocationIsPassedThrough(): void
    {
        $request = $this->minimalBuilder()
            ->withLocation('reception')
            ->build();

        self::assertSame('reception', $request->location);
    }

    // ----- required-field validation -----

    public function testMissingShipperFails(): void
    {
        $builder = $this->minimalBuilder(withShipper: false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('customerDetails.shipperDetails', $fields);
        }
    }

    public function testMissingPlannedDateTimeFails(): void
    {
        $builder = $this->minimalBuilder(withDateTime: false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('plannedPickupDateAndTime', $fields);
        }
    }

    public function testZeroAccountsFails(): void
    {
        $builder = $this->minimalBuilder(withAccount: false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('accounts', $fields);
        }
    }

    public function testMoreThanFiveAccountsFails(): void
    {
        $account = new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'));
        $builder = $this->minimalBuilder(withAccount: false);
        for ($i = 0; $i < 6; $i++) {
            $builder = $builder->withAccount($account);
        }

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('accounts', $fields);
        }
    }

    public function testZeroShipmentDetailsFails(): void
    {
        $builder = $this->minimalBuilder(withShipmentDetails: false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('shipmentDetails', $fields);
        }
    }

    // ----- time-window rules -----

    public function testPastPickupDateTimeFails(): void
    {
        $builder = $this->minimalBuilder(withDateTime: false)
            ->withPlannedPickupDateTime(new DateTimeImmutable('2020-01-01T10:00:00+00:00'));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('plannedPickupDateAndTime', $fields);
        }
    }

    public function testPickupDateTimeMoreThanTenDaysFails(): void
    {
        $builder = $this->minimalBuilder(withDateTime: false)
            ->withPlannedPickupDateTime(new DateTimeImmutable('+11 days'));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('plannedPickupDateAndTime', $fields);
        }
    }

    public function testPickupDateTimeWithinTenDaysSucceeds(): void
    {
        $request = $this->minimalBuilder(withDateTime: false)
            ->withPlannedPickupDateTime(new DateTimeImmutable('+3 days'))
            ->build();

        self::assertInstanceOf(CreatePickupRequest::class, $request);
    }

    public function testCloseTimeBeforePickupTimeFailsWhenBothProvided(): void
    {
        // Pickup at 14:00, closes at 12:00 — invalid
        $pickupTime = new DateTimeImmutable('tomorrow 14:00:00', new DateTimeZone('UTC'));

        $builder = $this->minimalBuilder(withDateTime: false)
            ->withPlannedPickupDateTime($pickupTime)
            ->withCloseTime('12:00');

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('closeTime', $fields);
        }
    }

    public function testCloseTimeAfterPickupTimeSucceeds(): void
    {
        // Pickup at 10:00, closes at 18:00 — valid
        $pickupTime = new DateTimeImmutable('tomorrow 10:00:00', new DateTimeZone('UTC'));

        $request = $this->minimalBuilder(withDateTime: false)
            ->withPlannedPickupDateTime($pickupTime)
            ->withCloseTime('18:00')
            ->build();

        self::assertSame('18:00', $request->closeTime);
    }

    public function testCloseTimeEqualToPickupTimeFailsWhenBothProvided(): void
    {
        // Pickup at 14:00, closes at 14:00 — courier needs time, so this is invalid
        $pickupTime = new DateTimeImmutable('tomorrow 14:00:00', new DateTimeZone('UTC'));

        $builder = $this->minimalBuilder(withDateTime: false)
            ->withPlannedPickupDateTime($pickupTime)
            ->withCloseTime('14:00');

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('closeTime', $fields);
        }
    }

    public function testNoCloseTimeDoesNotTriggerTimeWindowRule(): void
    {
        // closeTime not set — no comparison, no error
        $request = $this->minimalBuilder()->build();

        self::assertNull($request->closeTime);
    }

    // ----- multiple errors accumulated -----

    public function testMultipleValidationErrorsAreAllReported(): void
    {
        $builder = new PickupRequestBuilder();

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            // At minimum: shipper, plannedPickupDateAndTime, accounts, shipmentDetails
            self::assertContains('customerDetails.shipperDetails', $fields);
            self::assertContains('plannedPickupDateAndTime', $fields);
            self::assertContains('accounts', $fields);
            self::assertContains('shipmentDetails', $fields);
        }
    }

    // ----- helpers -----

    private function minimalBuilder(
        bool $withShipper = true,
        bool $withDateTime = true,
        bool $withAccount = true,
        bool $withShipmentDetails = true,
    ): PickupRequestBuilder {
        $builder = new PickupRequestBuilder();

        if ($withShipper) {
            $builder = $builder->withShipper(new ContactAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('14800'),
                cityName: 'Prague',
                addressLine1: 'Vaclavske namesti 1',
                phone: new PhoneNumber('+420 222 333 444'),
                companyName: 'Alfa Trading s.r.o.',
                fullName: 'Jan Nowak',
            ));
        }

        if ($withDateTime) {
            // Fixed 08:00 UTC avoids flakiness when wall-clock time exceeds a closeTime under test.
            $builder = $builder->withPlannedPickupDateTime(
                (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('+2 days')->setTime(8, 0),
            );
        }

        if ($withAccount) {
            $builder = $builder->withAccount(
                new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')),
            );
        }

        if ($withShipmentDetails) {
            $builder = $builder->withShipmentDetails(
                new PickupShipmentDetails(
                    productCode: 'N',
                    isCustomsDeclarable: false,
                    unitOfMeasurement: UnitSystem::Metric,
                    packages: [new PickupPackage(weight: new Weight(1.0, WeightUnit::KG))],
                ),
            );
        }

        return $builder;
    }
}

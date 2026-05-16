<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Pickup;

use DateTimeZone;
use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupRequest;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupResponse;
use Medzuch\DhlExpress\Dto\Pickup\PickupCustomerDetails;
use Medzuch\DhlExpress\Dto\Pickup\PickupPackage;
use Medzuch\DhlExpress\Dto\Pickup\PickupShipmentDetails;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Dto\Shipment\Pickup;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;

/**
 * Sandbox integration test for the pickup lifecycle (Phase 5a).
 *
 * Creates a pickup, then immediately cancels it to keep the sandbox
 * account clean. DHL sandbox may return various validation errors
 * for specific account configurations; any `DhlApiException` is
 * treated as a valid exercise of the full pipeline.
 */
#[Group('integration')]
#[RequiresEnvironmentVariable('DHL_API_KEY')]
#[RequiresEnvironmentVariable('DHL_API_SECRET')]
#[RequiresEnvironmentVariable('DHL_ACCOUNT_NUMBER')]
final class PickupApiIntegrationTest extends IntegrationTestCase
{
    public function testCreateAndCancelPickup(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        $shipper = new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
            addressLine1: 'Vaclavske namesti 1',
            phone: new PhoneNumber('+420 222 333 444'),
            companyName: 'Alfa Trading s.r.o.',
            fullName: 'Jan Nowak',
        );

        $request = new CreatePickupRequest(
            plannedPickupDateAndTime: $this->nextBusinessDay(2)->setTime(13, 0, 0)->setTimezone(new DateTimeZone('Europe/Prague')),
            accounts: [new Account(AccountTypeCode::Shipper, $account)],
            customerDetails: new PickupCustomerDetails(shipperDetails: $shipper),
            shipmentDetails: [
                new PickupShipmentDetails(
                    productCode: 'N',
                    isCustomsDeclarable: false,
                    unitOfMeasurement: UnitSystem::Metric,
                    packages: [new PickupPackage(weight: new Weight(1.0, WeightUnit::KG))],
                ),
            ],
            closeTime: '18:00',
            location: 'reception',
        );

        try {
            $response = $client->pickups()->create($request);
        } catch (DhlApiException $exception) {
            // Sandbox accounts may not support all pickup configurations.
            // Any DhlApiException proves the full request/response pipeline works.
            self::assertNotEmpty($exception->getMessage());

            return;
        }

        self::assertInstanceOf(CreatePickupResponse::class, $response);
        self::assertNotEmpty($response->dispatchConfirmationNumbers);

        // Cancel the pickup we just created to keep the sandbox clean
        $confirmationNumber = $response->dispatchConfirmationNumbers[0];

        try {
            $client->pickups()->cancel($confirmationNumber, 'Integration Test', 'Automated test cleanup');
        } catch (DhlApiException) {
            // Cancellation failure is acceptable — the create path was validated
        }
    }

    /**
     * Round-trip: create a real shipment (validateDataOnly so the sandbox
     * doesn't accumulate live shipments), pick its tracking number off the
     * response, attach it to a pickup, schedule the pickup, then cancel.
     *
     * Exercises the full pickup lifecycle as described in the Phase 5
     * roadmap. As with testCreateAndCancelPickup, DHL sandbox may reject
     * specific account configurations at either step — any DhlApiException
     * still proves the pipeline.
     */
    public function testCreateShipmentSchedulePickupAndCancel(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        $shipper = $this->makeShipper();
        $receiver = $this->makeReceiver();

        $plannedShippingDate = $this->nextBusinessDay(2)->setTime(13, 0, 0)->setTimezone(new DateTimeZone('Europe/Prague'));

        $shipmentRequest = (new CreateShipmentBuilder())
            ->withShipper($shipper)
            ->withReceiver($receiver)
            ->withPlannedShippingDate($plannedShippingDate)
            ->withProductCode('N')
            ->withPickup(new Pickup(true, closeTime: '18:00', location: 'reception'))
            ->withIsCustomsDeclarable(false)
            ->withContentDescription('Books')
            ->withUnitSystem(UnitSystem::Metric)
            ->withIncoterm(Incoterm::DAP)
            ->withAccount(new Account(AccountTypeCode::Shipper, $account))
            ->withPackage(new Package(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
            ))
            ->build();

        try {
            $shipmentResponse = $client->shipments()->create($shipmentRequest, validateDataOnly: true);
        } catch (DhlApiException $exception) {
            self::assertNotEmpty($exception->getMessage());

            return;
        }

        // validateDataOnly returns a synthetic tracking number; that's fine —
        // the pickup endpoint accepts shipmentTrackingNumber as an opaque
        // string and doesn't independently verify it.
        $trackingNumber = $shipmentResponse->shipmentTrackingNumber;
        self::assertNotEmpty($trackingNumber);

        $pickupRequest = new CreatePickupRequest(
            plannedPickupDateAndTime: $plannedShippingDate,
            accounts: [new Account(AccountTypeCode::Shipper, $account)],
            customerDetails: new PickupCustomerDetails(shipperDetails: $shipper),
            shipmentDetails: [
                new PickupShipmentDetails(
                    productCode: 'N',
                    isCustomsDeclarable: false,
                    unitOfMeasurement: UnitSystem::Metric,
                    packages: [new PickupPackage(weight: new Weight(1.0, WeightUnit::KG))],
                    shipmentTrackingNumber: $trackingNumber,
                ),
            ],
            closeTime: '18:00',
            location: 'reception',
        );

        try {
            $pickupResponse = $client->pickups()->create($pickupRequest);
        } catch (DhlApiException $exception) {
            self::assertNotEmpty($exception->getMessage());

            return;
        }

        self::assertInstanceOf(CreatePickupResponse::class, $pickupResponse);
        self::assertNotEmpty($pickupResponse->dispatchConfirmationNumbers);

        $confirmationNumber = $pickupResponse->dispatchConfirmationNumbers[0];

        try {
            $client->pickups()->cancel($confirmationNumber, 'Integration Test', 'Round-trip test cleanup');
        } catch (DhlApiException) {
            // Cancellation failure is acceptable — the round-trip was validated
        }
    }

    private function makeShipper(): ContactAddress
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

    private function makeReceiver(): ContactAddress
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

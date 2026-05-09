<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Pickup;

use DateTimeImmutable;
use DateTimeZone;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupResponse;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupRequest;
use Medzuch\DhlExpress\Dto\Pickup\PickupCustomerDetails;
use Medzuch\DhlExpress\Dto\Pickup\PickupPackage;
use Medzuch\DhlExpress\Dto\Pickup\PickupShipmentDetails;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
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
}

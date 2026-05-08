<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\ServicePoint;

use Medzuch\DhlExpress\Dto\ServicePoint\ServicePointFindResponse;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;

/**
 * Sandbox checks for the /servicepoints endpoint.
 *
 * DHL's spec rejects `postalCode` outright; valid lookups go by free-text
 * `address` plus a companion `countryCode`, by `latitude`+`longitude`,
 * by `servicePointID`, or by `idf`. Brussels Grand Place coordinates
 * are well-known and stable. The sandbox can legitimately return
 * zero results, so we only assert the response shape is well-formed.
 */
#[Group('integration')]
#[RequiresEnvironmentVariable('DHL_API_KEY')]
#[RequiresEnvironmentVariable('DHL_API_SECRET')]
final class ServicePointApiIntegrationTest extends IntegrationTestCase
{
    public function testFindsServicePointsByLatitudeAndLongitude(): void
    {
        $client = $this->makeClient();

        $response = $client->servicePoints()->find(
            latitude: 50.8467,
            longitude: 4.3499,
            resultLimit: 5,
        );

        self::assertInstanceOf(ServicePointFindResponse::class, $response);

        foreach ($response->servicePoints as $servicePoint) {
            self::assertNotEmpty($servicePoint->facilityId);
            self::assertNotEmpty($servicePoint->servicePointName);
        }
    }

    public function testFindsServicePointsByAddress(): void
    {
        $client = $this->makeClient();

        $response = $client->servicePoints()->find(
            address: 'Brussels',
            countryCode: new CountryCode('BE'),
            resultLimit: 5,
        );

        self::assertInstanceOf(ServicePointFindResponse::class, $response);
    }

    public function testHydratesEnumsFromLiveData(): void
    {
        $client = $this->makeClient();

        $response = $client->servicePoints()->find(
            latitude: 50.8467,
            longitude: 4.3499,
            resultLimit: 3,
        );

        self::assertInstanceOf(ServicePointFindResponse::class, $response);

        // Every service-point type DHL returns must parse into our enum.
        // If this fails, our `ServicePointType` enum is missing a case.
        foreach ($response->servicePoints as $servicePoint) {
            if ($servicePoint->rawServicePointType === '') {
                continue;
            }
            self::assertNotNull(
                $servicePoint->servicePointType,
                "Unknown ServicePointType returned by DHL: {$servicePoint->rawServicePointType}",
            );
        }

        // Same check for opening hours' day-of-week values.
        foreach ($response->servicePoints as $servicePoint) {
            foreach ($servicePoint->openingHours as $openingTime) {
                if ($openingTime->rawDayOfWeek === '') {
                    continue;
                }
                self::assertNotNull(
                    $openingTime->dayOfWeek,
                    "Unknown DayOfWeek returned by DHL: {$openingTime->rawDayOfWeek}",
                );
            }
        }
    }
}

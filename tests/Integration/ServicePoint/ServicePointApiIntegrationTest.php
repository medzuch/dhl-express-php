<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\ServicePoint;

use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use PHPUnit\Framework\Attributes\Group;

/**
 * Sandbox checks for the /servicepoints endpoint.
 *
 * Brussels (BE / 1000) is a stable lookup with multiple Service Points
 * in the DHL test environment, so the response always contains at
 * least one entry.
 */
#[Group('integration')]
final class ServicePointApiIntegrationTest extends IntegrationTestCase
{
    public function testFindsServicePointsByCountryAndPostalCode(): void
    {
        $client = $this->makeClient();

        $response = $client->servicePoints()->find(
            countryCode: new CountryCode('BE'),
            postalCode: new PostalCode('1000'),
            resultLimit: 5,
        );

        self::assertNotSame([], $response->servicePoints);

        $first = $response->servicePoints[0];
        self::assertNotEmpty($first->facilityId);
        self::assertNotEmpty($first->servicePointName);
    }

    public function testHydratesEnumsFromLiveData(): void
    {
        $client = $this->makeClient();

        $response = $client->servicePoints()->find(
            countryCode: new CountryCode('BE'),
            postalCode: new PostalCode('1000'),
            resultLimit: 3,
        );

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

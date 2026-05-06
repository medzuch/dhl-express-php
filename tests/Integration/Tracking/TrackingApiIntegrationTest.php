<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Tracking;

use Medzuch\DhlExpress\Dto\Tracking\TrackingResponse;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use PHPUnit\Framework\Attributes\Group;

/**
 * Hits the real DHL Express sandbox at express.api.dhl.com/mydhlapi/test.
 *
 * The test waybill numbers used here are the ones DHL publishes for
 * sandbox use; if DHL ever rotates them this test will start failing
 * with a DhlNotFoundException, at which point the constants below
 * need updating.
 */
#[Group('integration')]
final class TrackingApiIntegrationTest extends IntegrationTestCase
{
    private const KNOWN_TEST_WAYBILL = '9356579890';

    private const SECOND_KNOWN_TEST_WAYBILL = '4818240420';

    private const UNKNOWN_WAYBILL = '0000000000';

    public function testFetchesTrackingDetailsForKnownSandboxWaybill(): void
    {
        $client = $this->makeClient();

        $response = $client->tracking()->getByTrackingNumber(
            new TrackingNumber(self::KNOWN_TEST_WAYBILL),
        );

        self::assertInstanceOf(TrackingResponse::class, $response);
        self::assertSame(self::KNOWN_TEST_WAYBILL, $response->shipmentTrackingNumber);
        self::assertNotSame('', $response->status);
    }

    public function testRaisesNotFoundForUnknownWaybill(): void
    {
        $client = $this->makeClient();

        $this->expectException(DhlNotFoundException::class);

        $client->tracking()->getByTrackingNumber(
            new TrackingNumber(self::UNKNOWN_WAYBILL),
        );
    }

    public function testGetManyReturnsOneEntryPerSandboxWaybill(): void
    {
        $client = $this->makeClient();

        $responses = $client->tracking()->getMany(
            new TrackingNumber(self::KNOWN_TEST_WAYBILL),
            new TrackingNumber(self::SECOND_KNOWN_TEST_WAYBILL),
        );

        self::assertGreaterThanOrEqual(2, count($responses));
        $trackingNumbers = array_map(static fn (TrackingResponse $r): string => $r->shipmentTrackingNumber, $responses);
        self::assertContains(self::KNOWN_TEST_WAYBILL, $trackingNumbers);
        self::assertContains(self::SECOND_KNOWN_TEST_WAYBILL, $trackingNumbers);
    }
}

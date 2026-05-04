<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Tracking;

use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\DhlClient;
use Medzuch\DhlExpress\Dto\Tracking\TrackingResponse;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Hits the real DHL Express sandbox at express.api.dhl.com/mydhlapi/test.
 *
 * Skipped automatically unless both DHL_API_KEY (username) and
 * DHL_API_SECRET (password) are exported in the environment, so the
 * default `make test` run never reaches the network. Use
 * `make test-integration` to opt in.
 *
 * The test waybill numbers used here are the ones DHL publishes for
 * sandbox use; if DHL ever rotates them this test will start failing
 * with a DhlNotFoundException, at which point the constants below
 * need updating.
 */
#[Group('integration')]
final class TrackingApiIntegrationTest extends TestCase
{
    private const KNOWN_TEST_WAYBILL = '9356579890';

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

    private function makeClient(): DhlClient
    {
        $username = getenv('DHL_API_KEY');
        $password = getenv('DHL_API_SECRET');

        if (!is_string($username) || $username === '' || !is_string($password) || $password === '') {
            self::markTestSkipped('Set DHL_API_KEY and DHL_API_SECRET to run integration tests against the DHL sandbox.');
        }

        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials($username, $password),
        );

        return new DhlClient($config);
    }
}

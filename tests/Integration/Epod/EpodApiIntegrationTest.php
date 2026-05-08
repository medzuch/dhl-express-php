<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Epod;

use Medzuch\DhlExpress\Exception\DhlNotFoundException;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use PHPUnit\Framework\Attributes\Group;

/**
 * Sandbox check for the /shipments/{id}/proof-of-delivery endpoint.
 *
 * The DHL test environment does not host a real proof-of-delivery for
 * the public sample waybill, so this test exercises the error path:
 * a 404 with `detail: "No data found"` is the expected outcome and
 * confirms the exception mapping. If DHL ever provisions real EPoD
 * data on the sandbox, the test still passes by branching to the
 * success path.
 */
#[Group('integration')]
final class EpodApiIntegrationTest extends IntegrationTestCase
{
    public function testFetchesProofOfDeliveryOrSurfacesNotFound(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        try {
            $response = $client->epod()->get(
                new TrackingNumber('1234567890'),
                $account,
            );
        } catch (DhlNotFoundException $exception) {
            self::assertSame(404, $exception->httpStatus);
            self::assertNotNull($exception->dhlMessage);

            return;
        }

        // Success path — only reachable if DHL provisions a real EPoD on
        // sandbox; assert the response shape is hydrated correctly.
        foreach ($response->documents as $document) {
            self::assertNotEmpty($document->encodingFormat);
            self::assertNotEmpty($document->typeCode);
        }
    }
}

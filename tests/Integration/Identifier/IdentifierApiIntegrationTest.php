<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Identifier;

use Medzuch\DhlExpress\Enum\IdentifierType;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Sandbox check for the /identifiers endpoint.
 *
 * Requires `DHL_ACCOUNT_NUMBER` on top of the basic-auth env vars.
 * The endpoint additionally requires DHL-side breakbulk
 * authorization on the account; if the test account is not
 * authorized, DHL returns the documented `3501` (Request
 * Identifier is not allowed for this Username) error and the
 * test passes via the exception path.
 */
#[Group('integration')]
final class IdentifierApiIntegrationTest extends IntegrationTestCase
{
    public function testAllocatesSidIdentifiersOrSurfacesAuthorizationError(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        try {
            $response = $client->identifier()->allocate($account, IdentifierType::SID, 1);
        } catch (DhlApiException $exception) {
            // DHL signals "account not authorized for breakbulk identifier
            // reservation" as HTTP 400 with `status: "400"` in the body and
            // `3501` embedded in the `detail` message — so we match on the
            // human-readable message rather than `dhlErrorCode`.
            self::assertNotNull($exception->dhlMessage);
            self::assertStringContainsString('3501', $exception->dhlMessage);

            return;
        }

        self::assertNotSame([], $response->identifiers);
        self::assertSame(IdentifierType::SID, $response->identifiers[0]->typeCode);
        self::assertCount(1, $response->identifiers[0]->list);
    }
}

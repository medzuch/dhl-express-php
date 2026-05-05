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
            // 3501: account not authorized for breakbulk identifier reservation.
            self::assertSame('3501', $exception->dhlErrorCode);

            return;
        }

        self::assertNotSame([], $response->identifiers);
        self::assertSame(IdentifierType::SID, $response->identifiers[0]->typeCode);
        self::assertCount(1, $response->identifiers[0]->list);
    }
}

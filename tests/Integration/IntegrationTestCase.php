<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration;

use DateTimeImmutable;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\DhlClient;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Tests\Integration\Logging\JsonLineFileLogger;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use PHPUnit\Framework\TestCase;

/**
 * Shared scaffolding for tests that hit the real DHL Express sandbox.
 *
 * Env-var presence is enforced declaratively via
 * `#[RequiresEnvironmentVariable]` on each integration test class —
 * PHPUnit skips the test before invoking it when a required variable
 * is missing, so `makeClient()` and `requireAccountNumber()` can
 * trust their preconditions and just construct values.
 */
abstract class IntegrationTestCase extends TestCase
{
    final protected function makeClient(): DhlClient
    {
        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials(
                username: (string) getenv('DHL_API_KEY'),
                password: (string) getenv('DHL_API_SECRET'),
            ),
        );

        return new DhlClient($config, logger: $this->makeIntegrationLogger());
    }

    private function makeIntegrationLogger(): JsonLineFileLogger
    {
        $reflection = new \ReflectionClass(static::class);
        $shortName = $reflection->getShortName();
        $namespaceTail = basename(str_replace('\\', '/', $reflection->getNamespaceName()));
        $testName = $this->name();

        $path = sprintf(
            '%s/var/integration-logs/%s/%s/%s.log',
            dirname(__DIR__, 2),
            $namespaceTail,
            $shortName,
            $testName,
        );

        return new JsonLineFileLogger($path);
    }

    final protected function requireAccountNumber(): AccountNumber
    {
        return new AccountNumber((string) getenv('DHL_ACCOUNT_NUMBER'));
    }

    /**
     * Returns a date at least `$minDaysAhead` days from today, shifted
     * forward to the next Monday whenever it falls on a Saturday or
     * Sunday. Public DHL holidays still slip through, but Mon–Fri is
     * enough to keep `+5 days`-style tests off the weekend cliff that
     * would otherwise return a 996 ("products not available for pickup
     * date") on roughly two days a week.
     */
    final protected function nextBusinessDay(int $minDaysAhead): DateTimeImmutable
    {
        $date = new DateTimeImmutable('+' . $minDaysAhead . ' days');
        $dow = (int) $date->format('N');

        return match ($dow) {
            6 => $date->modify('+2 days'),
            7 => $date->modify('+1 day'),
            default => $date,
        };
    }
}

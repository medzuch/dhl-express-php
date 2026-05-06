<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration;

use DateTimeImmutable;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\DhlClient;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use PHPUnit\Framework\TestCase;

/**
 * Shared scaffolding for tests that hit the real DHL Express sandbox.
 *
 * `makeClient()` skips the test when `DHL_API_KEY` / `DHL_API_SECRET`
 * are absent so the default `make test` run never reaches the
 * network. `requireAccountNumber()` skips the test when
 * `DHL_ACCOUNT_NUMBER` is absent — useful for the endpoints that
 * need a customer account on top of basic auth.
 */
abstract class IntegrationTestCase extends TestCase
{
    final protected function makeClient(): DhlClient
    {
        $username = getenv('DHL_API_KEY');
        $password = getenv('DHL_API_SECRET');

        if (!is_string($username) || $username === '' || !is_string($password) || $password === '') {
            self::markTestSkipped(
                'Set DHL_API_KEY and DHL_API_SECRET to run integration tests against the DHL sandbox.',
            );
        }

        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials($username, $password),
        );

        return new DhlClient($config);
    }

    final protected function requireAccountNumber(): AccountNumber
    {
        $value = getenv('DHL_ACCOUNT_NUMBER');

        if (!is_string($value) || $value === '') {
            self::markTestSkipped('Set DHL_ACCOUNT_NUMBER to run this integration test.');
        }

        return new AccountNumber($value);
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

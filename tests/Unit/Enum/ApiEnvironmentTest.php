<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ApiEnvironment;
use PHPUnit\Framework\TestCase;

final class ApiEnvironmentTest extends TestCase
{
    public function testSandboxBaseUrlMatchesDhlSpec(): void
    {
        self::assertSame(
            'https://express.api.dhl.com/mydhlapi/test',
            ApiEnvironment::Sandbox->baseUrl(),
        );
    }

    public function testProductionBaseUrlMatchesDhlSpec(): void
    {
        self::assertSame(
            'https://express.api.dhl.com/mydhlapi',
            ApiEnvironment::Production->baseUrl(),
        );
    }

    public function testCasesAreBackedByStableStringValues(): void
    {
        self::assertSame('sandbox', ApiEnvironment::Sandbox->value);
        self::assertSame('production', ApiEnvironment::Production->value);
    }

    public function testFromValueRoundTrips(): void
    {
        self::assertSame(ApiEnvironment::Sandbox, ApiEnvironment::from('sandbox'));
        self::assertSame(ApiEnvironment::Production, ApiEnvironment::from('production'));
    }

    public function testIsSandboxFlagReflectsCase(): void
    {
        self::assertTrue(ApiEnvironment::Sandbox->isSandbox());
        self::assertFalse(ApiEnvironment::Production->isSandbox());
    }
}

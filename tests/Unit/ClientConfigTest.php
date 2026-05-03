<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit;

use InvalidArgumentException;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\ValueObject\IntegrationProfile;
use Medzuch\DhlExpress\ValueObject\PlatformIdentifier;
use PHPUnit\Framework\TestCase;

final class ClientConfigTest extends TestCase
{
    public function testExposesEnvironmentAndCredentials(): void
    {
        $credentials = new Credentials('user', 'pass');
        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: $credentials,
        );

        self::assertSame(ApiEnvironment::Sandbox, $config->environment);
        self::assertSame($credentials, $config->credentials);
    }

    public function testDefaultsMatchDhlSpec(): void
    {
        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
        );

        self::assertSame(30.0, $config->timeout);
        self::assertSame('eng', $config->acceptLanguage);
        self::assertSame('3.2.0', $config->xVersion);
        self::assertNull($config->integrationProfile);
    }

    public function testAcceptsIntegrationProfile(): void
    {
        $profile = new IntegrationProfile(
            plugin: new PlatformIdentifier(name: 'AcmePlugin', version: '1.0'),
        );
        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
            integrationProfile: $profile,
        );

        self::assertSame($profile, $config->integrationProfile);
    }

    public function testCustomValuesAreRespected(): void
    {
        $config = new ClientConfig(
            environment: ApiEnvironment::Production,
            credentials: new Credentials('user', 'pass'),
            timeout: 10.5,
            acceptLanguage: 'fra',
            xVersion: '3.2.0',
        );

        self::assertSame(10.5, $config->timeout);
        self::assertSame('fra', $config->acceptLanguage);
        self::assertSame('3.2.0', $config->xVersion);
    }

    public function testBaseUrlDelegatesToEnvironment(): void
    {
        $sandbox = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
        );
        $production = new ClientConfig(
            environment: ApiEnvironment::Production,
            credentials: new Credentials('user', 'pass'),
        );

        self::assertSame('https://express.api.dhl.com/mydhlapi/test', $sandbox->baseUrl());
        self::assertSame('https://express.api.dhl.com/mydhlapi', $production->baseUrl());
    }

    public function testRejectsNonPositiveTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be positive');

        new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
            timeout: 0.0,
        );
    }

    public function testRejectsNegativeTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be positive');

        new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
            timeout: -1.0,
        );
    }

    public function testRejectsAcceptLanguageNotThreeChars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Accept-Language must be a 3-character language code');

        new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
            acceptLanguage: 'en',
        );
    }

    public function testRejectsEmptyXVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('x-version must not be empty');

        new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
            xVersion: '',
        );
    }
}

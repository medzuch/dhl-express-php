<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\IntegrationProfile;
use Medzuch\DhlExpress\ValueObject\PlatformIdentifier;
use PHPUnit\Framework\TestCase;

final class IntegrationProfileTest extends TestCase
{
    public function testHoldsThreeOptionalIdentifiers(): void
    {
        $plugin = new PlatformIdentifier(name: 'AcmePlugin', version: '1.0');
        $shipping = new PlatformIdentifier(name: 'AcmeShipper', version: '2.4');
        $webstore = new PlatformIdentifier(name: 'AcmeStore', version: '3.1');

        $profile = new IntegrationProfile(
            plugin: $plugin,
            shippingSystem: $shipping,
            webstore: $webstore,
        );

        self::assertSame($plugin, $profile->plugin);
        self::assertSame($shipping, $profile->shippingSystem);
        self::assertSame($webstore, $profile->webstore);
    }

    public function testAcceptsAnySingleSlotPopulated(): void
    {
        $plugin = new PlatformIdentifier(name: 'AcmePlugin', version: '1.0');

        $profile = new IntegrationProfile(plugin: $plugin);

        self::assertSame($plugin, $profile->plugin);
        self::assertNull($profile->shippingSystem);
        self::assertNull($profile->webstore);
    }

    public function testAcceptsOnlyShippingSystem(): void
    {
        $shipping = new PlatformIdentifier(name: 'AcmeShipper', version: '2.4');

        $profile = new IntegrationProfile(shippingSystem: $shipping);

        self::assertNull($profile->plugin);
        self::assertSame($shipping, $profile->shippingSystem);
        self::assertNull($profile->webstore);
    }

    public function testAcceptsOnlyWebstore(): void
    {
        $webstore = new PlatformIdentifier(name: 'AcmeStore', version: '3.1');

        $profile = new IntegrationProfile(webstore: $webstore);

        self::assertNull($profile->plugin);
        self::assertNull($profile->shippingSystem);
        self::assertSame($webstore, $profile->webstore);
    }

    public function testRejectsAllNullSlots(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IntegrationProfile must set at least one of plugin, shippingSystem, webstore');

        new IntegrationProfile();
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;

/**
 * 3PV (third-party vendor) identification used for the optional
 * Plugin-*, Shipping-System-Platform-*, and Webstore-Platform-*
 * request headers.
 *
 * At least one slot must be populated — otherwise constructing
 * the profile would be a no-op and the caller should have passed
 * null to {@see \Medzuch\DhlExpress\ClientConfig} instead.
 */
final readonly class IntegrationProfile
{
    public function __construct(
        public ?PlatformIdentifier $plugin = null,
        public ?PlatformIdentifier $shippingSystem = null,
        public ?PlatformIdentifier $webstore = null,
    ) {
        if ($plugin === null && $shippingSystem === null && $webstore === null) {
            throw new InvalidArgumentException(
                'IntegrationProfile must set at least one of plugin, shippingSystem, webstore',
            );
        }
    }
}

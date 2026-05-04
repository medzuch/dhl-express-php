<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\ValueObject;

use InvalidArgumentException;

/**
 * Name + version pair identifying a 3PV plugin or platform.
 *
 * Length limits come from `dhl_openapi.yaml`: Plugin/Shipping-System/
 * Webstore platform name headers cap at 20 characters and version
 * headers cap at 15 characters.
 */
final readonly class PlatformIdentifier
{
    public function __construct(
        public string $name,
        public string $version,
    ) {
        if ($name === '') {
            throw new InvalidArgumentException('Platform name must not be empty');
        }

        if (strlen($name) > 20) {
            throw new InvalidArgumentException('Platform name must be at most 20 characters');
        }

        if ($version === '') {
            throw new InvalidArgumentException('Platform version must not be empty');
        }

        if (strlen($version) > 15) {
            throw new InvalidArgumentException('Platform version must be at most 15 characters');
        }
    }
}

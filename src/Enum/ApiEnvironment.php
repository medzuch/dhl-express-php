<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express MyDHL API environment.
 *
 * Base URLs from `specs/dhl/dpdhl-express-api-3.4.0_9.yaml` (`servers:` section).
 */
enum ApiEnvironment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';

    public function baseUrl(): string
    {
        return match ($this) {
            self::Sandbox => 'https://express.api.dhl.com/mydhlapi/test',
            self::Production => 'https://express.api.dhl.com/mydhlapi',
        };
    }

    public function isSandbox(): bool
    {
        return $this === self::Sandbox;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress;

use InvalidArgumentException;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\Enum\ApiEnvironment;

/**
 * Top-level configuration for {@see DhlClient}.
 *
 * Holds the environment selection, basic-auth credentials, and the
 * defaults used by every outbound request (timeout, Accept-Language,
 * x-version). 3PV plugin/platform identification headers are added
 * later via a dedicated VO once the request builder is in place.
 */
final readonly class ClientConfig
{
    public function __construct(
        public ApiEnvironment $environment,
        public Credentials $credentials,
        public float $timeout = 30.0,
        public string $acceptLanguage = 'eng',
        public string $xVersion = '3.2.0',
    ) {
        if ($timeout <= 0.0) {
            throw new InvalidArgumentException('Timeout must be positive');
        }

        if (strlen($acceptLanguage) !== 3) {
            throw new InvalidArgumentException('Accept-Language must be a 3-character language code');
        }

        if ($xVersion === '') {
            throw new InvalidArgumentException('x-version must not be empty');
        }
    }

    public function baseUrl(): string
    {
        return $this->environment->baseUrl();
    }
}

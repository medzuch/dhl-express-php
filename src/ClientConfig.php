<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress;

use InvalidArgumentException;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\ValueObject\IntegrationProfile;

/**
 * Top-level configuration for {@see DhlClient}.
 *
 * Holds the environment selection, basic-auth credentials, and the
 * defaults used by every outbound request (Accept-Language,
 * x-version). An optional {@see IntegrationProfile} carries the 3PV
 * plugin/platform identification headers DHL surfaces in the spec as
 * "applicable to 3PV only".
 */
final readonly class ClientConfig
{
    public function __construct(
        public ApiEnvironment $environment,
        public Credentials $credentials,
        public string $acceptLanguage = 'eng',
        public string $xVersion = '3.2.0',
        public ?IntegrationProfile $integrationProfile = null,
    ) {
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

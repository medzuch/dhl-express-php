<?php

declare(strict_types=1);

/**
 * Shared bootstrap for all examples.
 *
 * Run any example from the project root:
 *
 *   DHL_API_KEY=x DHL_API_SECRET=y DHL_ACCOUNT_NUMBER=z php examples/track-shipment.php
 *
 * Or copy .env.example to .env, fill it in, and source it first:
 *
 *   set -a && source .env && set +a
 *   php examples/track-shipment.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\DhlClient;
use Medzuch\DhlExpress\Enum\ApiEnvironment;

function requireEnv(string $name): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        fwrite(STDERR, "Missing required environment variable: {$name}\n");
        exit(1);
    }

    return $value;
}

function makeClient(): DhlClient
{
    return new DhlClient(new ClientConfig(
        environment: ApiEnvironment::Sandbox,
        credentials: new Credentials(
            username: requireEnv('DHL_API_KEY'),
            password: requireEnv('DHL_API_SECRET'),
        ),
    ));
}

function accountNumber(): string
{
    return requireEnv('DHL_ACCOUNT_NUMBER');
}

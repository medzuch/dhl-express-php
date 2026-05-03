<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Auth;

use InvalidArgumentException;

/**
 * DHL Express API basic-auth credentials.
 *
 * The MyDHL API uses HTTP Basic Authentication. RFC 7617 forbids
 * a colon in the username; we reject it at construction.
 */
final readonly class Credentials
{
    public function __construct(
        public string $username,
        public string $password,
    ) {
        if ($username === '') {
            throw new InvalidArgumentException('Username must not be empty');
        }

        if ($password === '') {
            throw new InvalidArgumentException('Password must not be empty');
        }

        if (str_contains($username, ':')) {
            throw new InvalidArgumentException('Username must not contain a colon (RFC 7617)');
        }
    }

    public function toBasicAuthHeader(): string
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }

    /**
     * @return array{username: string, password: string}
     */
    public function __debugInfo(): array
    {
        return [
            'username' => $this->username,
            'password' => '***',
        ];
    }
}

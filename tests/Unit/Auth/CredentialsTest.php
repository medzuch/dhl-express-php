<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Auth;

use InvalidArgumentException;
use Medzuch\DhlExpress\Auth\Credentials;
use PHPUnit\Framework\TestCase;

final class CredentialsTest extends TestCase
{
    public function testExposesUsernameAndPassword(): void
    {
        $creds = new Credentials('apiUser123', 's3cret');

        self::assertSame('apiUser123', $creds->username);
        self::assertSame('s3cret', $creds->password);
    }

    public function testProducesRfc7617BasicAuthHeaderValue(): void
    {
        $creds = new Credentials('Aladdin', 'open sesame');

        // RFC 7617 sample: base64('Aladdin:open sesame') === 'QWxhZGRpbjpvcGVuIHNlc2FtZQ=='
        self::assertSame(
            'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
            $creds->toBasicAuthHeader(),
        );
    }

    public function testRejectsEmptyUsername(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Username must not be empty');

        new Credentials('', 'password');
    }

    public function testRejectsEmptyPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must not be empty');

        new Credentials('user', '');
    }

    public function testRejectsUsernameContainingColonPerRfc7617(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Username must not contain a colon');

        new Credentials('user:name', 'password');
    }

    public function testDebugInfoRedactsPassword(): void
    {
        $creds = new Credentials('apiUser', 's3cret');

        $debug = $creds->__debugInfo();

        self::assertSame('apiUser', $debug['username']);
        self::assertSame('***', $debug['password']);
    }
}

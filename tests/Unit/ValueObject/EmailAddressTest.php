<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\EmailAddress;
use PHPUnit\Framework\TestCase;

final class EmailAddressTest extends TestCase
{
    public function testAcceptsACanonicalAddress(): void
    {
        $email = new EmailAddress('shipper@example.com');

        self::assertSame('shipper@example.com', $email->value);
    }

    public function testStringifiesToItsValue(): void
    {
        self::assertSame('shipper@example.com', (string) new EmailAddress('shipper@example.com'));
    }

    public function testEqualityIsCaseSensitive(): void
    {
        self::assertTrue((new EmailAddress('a@b.com'))->equals(new EmailAddress('a@b.com')));
        self::assertFalse((new EmailAddress('a@b.com'))->equals(new EmailAddress('A@b.com')));
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EmailAddress('');
    }

    public function testRejectsAddressWithoutAtSign(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('email');

        new EmailAddress('not-an-email');
    }

    public function testRejectsAddressOver70Chars(): void
    {
        $longLocal = str_repeat('a', 64);
        $address = $longLocal . '@b.com'; // 64 + 6 = 70, valid edge
        $address71 = $longLocal . '@bb.com'; // 71

        new EmailAddress($address);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('70');

        new EmailAddress($address71);
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTest extends TestCase
{
    public function testAcceptsAnInternationalNumber(): void
    {
        $phone = new PhoneNumber('+48123456789');

        self::assertSame('+48123456789', $phone->value);
    }

    public function testStringifiesToItsValue(): void
    {
        self::assertSame('+48123456789', (string) new PhoneNumber('+48123456789'));
    }

    public function testEquality(): void
    {
        self::assertTrue((new PhoneNumber('+1555'))->equals(new PhoneNumber('+1555')));
        self::assertFalse((new PhoneNumber('+1555'))->equals(new PhoneNumber('+1556')));
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PhoneNumber('');
    }

    public function testRejectsValueOver70Chars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('70');

        new PhoneNumber(str_repeat('1', 71));
    }
}

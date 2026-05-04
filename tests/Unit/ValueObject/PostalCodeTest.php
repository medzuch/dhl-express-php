<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use PHPUnit\Framework\TestCase;

final class PostalCodeTest extends TestCase
{
    public function testAcceptsACommonZipCode(): void
    {
        $postal = new PostalCode('27801');

        self::assertSame('27801', $postal->value);
    }

    public function testAcceptsAlphanumericFormatsLikeUkAndCanada(): void
    {
        self::assertSame('SW1A 1AA', (new PostalCode('SW1A 1AA'))->value);
        self::assertSame('K1A 0B1', (new PostalCode('K1A 0B1'))->value);
    }

    public function testAcceptsEmptyForCountriesWithoutPostalCodes(): void
    {
        $postal = new PostalCode('');

        self::assertSame('', $postal->value);
    }

    public function testStringifiesToItsValue(): void
    {
        self::assertSame('27801', (string) new PostalCode('27801'));
    }

    public function testEquality(): void
    {
        self::assertTrue((new PostalCode('27801'))->equals(new PostalCode('27801')));
        self::assertFalse((new PostalCode('27801'))->equals(new PostalCode('27802')));
    }

    public function testRejectsValueOver12Chars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('12');

        new PostalCode(str_repeat('1', 13));
    }
}

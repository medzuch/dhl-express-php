<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use PHPUnit\Framework\TestCase;

final class CountryCodeTest extends TestCase
{
    public function testAcceptsTwoUppercaseLetters(): void
    {
        $code = new CountryCode('PL');

        self::assertSame('PL', $code->value);
    }

    public function testStringifiesToItsValue(): void
    {
        self::assertSame('US', (string) new CountryCode('US'));
    }

    public function testEqualityIsValueBased(): void
    {
        self::assertTrue((new CountryCode('DE'))->equals(new CountryCode('DE')));
        self::assertFalse((new CountryCode('DE'))->equals(new CountryCode('FR')));
    }

    public function testRejectsLowercase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ISO 3166-1 alpha-2');

        new CountryCode('pl');
    }

    public function testRejectsThreeLetters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CountryCode('POL');
    }

    public function testRejectsOneLetter(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CountryCode('P');
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CountryCode('');
    }

    public function testRejectsDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CountryCode('P1');
    }
}

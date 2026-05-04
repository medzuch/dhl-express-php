<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use PHPUnit\Framework\TestCase;

final class CurrencyCodeTest extends TestCase
{
    public function testAcceptsThreeUppercaseLetters(): void
    {
        $code = new CurrencyCode('USD');

        self::assertSame('USD', $code->value);
    }

    public function testStringifiesToItsValue(): void
    {
        self::assertSame('EUR', (string) new CurrencyCode('EUR'));
    }

    public function testEqualityIsValueBased(): void
    {
        self::assertTrue((new CurrencyCode('PLN'))->equals(new CurrencyCode('PLN')));
        self::assertFalse((new CurrencyCode('PLN'))->equals(new CurrencyCode('USD')));
    }

    public function testRejectsLowercase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ISO 4217');

        new CurrencyCode('usd');
    }

    public function testRejectsTwoLetters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CurrencyCode('US');
    }

    public function testRejectsFourLetters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CurrencyCode('USDX');
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CurrencyCode('');
    }

    public function testRejectsDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CurrencyCode('US1');
    }
}

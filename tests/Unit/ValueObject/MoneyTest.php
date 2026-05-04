<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use Medzuch\DhlExpress\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testStoresAmountAndCurrency(): void
    {
        $money = new Money(99.99, new CurrencyCode('EUR'));

        self::assertSame(99.99, $money->amount);
        self::assertSame('EUR', $money->currency->value);
    }

    public function testAcceptsZeroAndNegativeAmounts(): void
    {
        $zero = new Money(0.0, new CurrencyCode('USD'));
        $negative = new Money(-10.5, new CurrencyCode('USD'));

        self::assertSame(0.0, $zero->amount);
        self::assertSame(-10.5, $negative->amount);
    }

    public function testEqualityRequiresAmountAndCurrencyMatch(): void
    {
        $a = new Money(10.0, new CurrencyCode('PLN'));
        $b = new Money(10.0, new CurrencyCode('PLN'));
        $c = new Money(10.0, new CurrencyCode('EUR'));
        $d = new Money(11.0, new CurrencyCode('PLN'));

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
        self::assertFalse($a->equals($d));
    }

    public function testRejectsNonFiniteAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('finite');

        new Money(\INF, new CurrencyCode('USD'));
    }

    public function testRejectsNan(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(\NAN, new CurrencyCode('USD'));
    }
}

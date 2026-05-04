<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\ServiceAreaCode;
use PHPUnit\Framework\TestCase;

final class ServiceAreaCodeTest extends TestCase
{
    public function testAcceptsThreeUppercaseLetters(): void
    {
        $code = new ServiceAreaCode('SYD');

        self::assertSame('SYD', $code->value);
    }

    public function testStringifiesToItsValue(): void
    {
        self::assertSame('PRG', (string) new ServiceAreaCode('PRG'));
    }

    public function testEquality(): void
    {
        self::assertTrue((new ServiceAreaCode('AKL'))->equals(new ServiceAreaCode('AKL')));
        self::assertFalse((new ServiceAreaCode('AKL'))->equals(new ServiceAreaCode('SYD')));
    }

    public function testRejectsLowercase(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceAreaCode('syd');
    }

    public function testRejectsTwoLetters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceAreaCode('SY');
    }

    public function testRejectsFourLetters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceAreaCode('SYDX');
    }

    public function testRejectsDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceAreaCode('SY1');
    }

    public function testRejectsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ServiceAreaCode('');
    }
}

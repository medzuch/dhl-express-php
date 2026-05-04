<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\HsCode;
use PHPUnit\Framework\TestCase;

final class HsCodeTest extends TestCase
{
    public function testAcceptsSixDigitCode(): void
    {
        $code = new HsCode('851713');

        self::assertSame('851713', $code->value);
    }

    public function testAcceptsTenDigitCode(): void
    {
        $code = new HsCode('8517130000');

        self::assertSame('8517130000', $code->value);
    }

    public function testAcceptsDottedFormat(): void
    {
        $code = new HsCode('8517.13.00');

        self::assertSame('8517.13.00', $code->value);
    }

    public function testStringifiesToItsValue(): void
    {
        self::assertSame('851713', (string) new HsCode('851713'));
    }

    public function testEquality(): void
    {
        self::assertTrue((new HsCode('851713'))->equals(new HsCode('851713')));
        self::assertFalse((new HsCode('851713'))->equals(new HsCode('851714')));
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HsCode('');
    }

    public function testRejectsLetters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('digits');

        new HsCode('ABC123');
    }

    public function testRejectsValueOver18Chars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('18');

        new HsCode(str_repeat('1', 19));
    }
}

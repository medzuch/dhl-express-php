<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use PHPUnit\Framework\TestCase;

final class AccountNumberTest extends TestCase
{
    public function testAcceptsADhlAccountNumber(): void
    {
        $account = new AccountNumber('123456789');

        self::assertSame('123456789', $account->value);
    }

    public function testStringifiesToItsValue(): void
    {
        self::assertSame('123456789', (string) new AccountNumber('123456789'));
    }

    public function testEquality(): void
    {
        self::assertTrue((new AccountNumber('111'))->equals(new AccountNumber('111')));
        self::assertFalse((new AccountNumber('111'))->equals(new AccountNumber('222')));
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty');

        new AccountNumber('');
    }

    public function testDebugInfoMasksAllButLastFourDigits(): void
    {
        $account = new AccountNumber('123456789');

        $dump = print_r($account, true);

        self::assertStringContainsString('*****6789', $dump);
        self::assertStringNotContainsString('123456789', $dump);
    }

    public function testDebugInfoMasksEveryCharacterForShortValues(): void
    {
        // Length <= 4: full mask (no last-four window long enough to keep)
        $account = new AccountNumber('1234');

        $dump = print_r($account, true);

        self::assertStringContainsString('****', $dump);
        self::assertStringNotContainsString('1234', $dump);
    }
}

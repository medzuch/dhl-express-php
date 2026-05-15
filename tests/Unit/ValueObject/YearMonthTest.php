<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\YearMonth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class YearMonthTest extends TestCase
{
    public function testExposesValue(): void
    {
        $ym = new YearMonth('2026-05');

        self::assertSame('2026-05', $ym->value);
    }

    public function testCastsToStringYieldsTheRawValue(): void
    {
        self::assertSame('2026-12', (string) new YearMonth('2026-12'));
    }

    public function testFromDateTime(): void
    {
        $ym = YearMonth::fromDateTime(new DateTimeImmutable('2026-05-09 13:00:00'));

        self::assertSame('2026-05', $ym->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validValues(): iterable
    {
        yield 'january' => ['2026-01'];
        yield 'december' => ['2026-12'];
        yield 'past year' => ['1999-07'];
        yield 'far future' => ['9999-11'];
    }

    #[DataProvider('validValues')]
    public function testAcceptsValidValues(string $value): void
    {
        self::assertSame($value, (new YearMonth($value))->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidValues(): iterable
    {
        yield 'empty' => [''];
        yield 'no zero pad' => ['2026-1'];
        yield 'slash separator' => ['2026/01'];
        yield 'month 13' => ['2026-13'];
        yield 'month 00' => ['2026-00'];
        yield 'three-digit year' => ['202-05'];
        yield 'full date' => ['2026-05-09'];
        yield 'junk' => ['nope'];
    }

    #[DataProvider('invalidValues')]
    public function testRejectsInvalidValues(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new YearMonth($value);
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        self::assertTrue((new YearMonth('2026-05'))->equals(new YearMonth('2026-05')));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        self::assertFalse((new YearMonth('2026-05'))->equals(new YearMonth('2026-06')));
    }
}

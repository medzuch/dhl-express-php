<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DayOfWeek;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DayOfWeekTest extends TestCase
{
    /**
     * @return iterable<string, array{DayOfWeek, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'MONDAY' => [DayOfWeek::Monday, 'MONDAY'];
        yield 'TUESDAY' => [DayOfWeek::Tuesday, 'TUESDAY'];
        yield 'WEDNESDAY' => [DayOfWeek::Wednesday, 'WEDNESDAY'];
        yield 'THURSDAY' => [DayOfWeek::Thursday, 'THURSDAY'];
        yield 'FRIDAY' => [DayOfWeek::Friday, 'FRIDAY'];
        yield 'SATURDAY' => [DayOfWeek::Saturday, 'SATURDAY'];
        yield 'SUNDAY' => [DayOfWeek::Sunday, 'SUNDAY'];
        yield 'HOLIDAY' => [DayOfWeek::Holiday, 'HOLIDAY'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(DayOfWeek $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllSevenWeekdaysPlusHoliday(): void
    {
        self::assertCount(8, DayOfWeek::cases());
    }
}

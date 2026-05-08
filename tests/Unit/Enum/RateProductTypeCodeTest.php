<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\RateProductTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RateProductTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{RateProductTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'all' => [RateProductTypeCode::All, 'all'];
        yield 'dayDefinite' => [RateProductTypeCode::DayDefinite, 'dayDefinite'];
        yield 'timeDefinite' => [RateProductTypeCode::TimeDefinite, 'timeDefinite'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(RateProductTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllThreeCases(): void
    {
        self::assertCount(3, RateProductTypeCode::cases());
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\RateCurrencyType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RateCurrencyTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{RateCurrencyType, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'BILLC' => [RateCurrencyType::BILLC, 'BILLC'];
        yield 'PULCL' => [RateCurrencyType::PULCL, 'PULCL'];
        yield 'BASEC' => [RateCurrencyType::BASEC, 'BASEC'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(RateCurrencyType $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllThreeCases(): void
    {
        self::assertCount(3, RateCurrencyType::cases());
    }
}

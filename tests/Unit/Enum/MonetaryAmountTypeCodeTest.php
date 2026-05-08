<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\MonetaryAmountTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MonetaryAmountTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{MonetaryAmountTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'declaredValue' => [MonetaryAmountTypeCode::DeclaredValue, 'declaredValue'];
        yield 'insuredValue' => [MonetaryAmountTypeCode::InsuredValue, 'insuredValue'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(MonetaryAmountTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversBothCases(): void
    {
        self::assertCount(2, MonetaryAmountTypeCode::cases());
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\LineItemQuantityType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LineItemQuantityTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{LineItemQuantityType, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'prt' => [LineItemQuantityType::Part, 'prt'];
        yield 'box' => [LineItemQuantityType::Box, 'box'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(LineItemQuantityType $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversBothCases(): void
    {
        self::assertCount(2, LineItemQuantityType::cases());
    }
}

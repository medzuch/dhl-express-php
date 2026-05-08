<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\RateNetworkTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RateNetworkTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{RateNetworkTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'DD' => [RateNetworkTypeCode::DD, 'DD'];
        yield 'TD' => [RateNetworkTypeCode::TD, 'TD'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(RateNetworkTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversBothCases(): void
    {
        self::assertCount(2, RateNetworkTypeCode::cases());
    }
}

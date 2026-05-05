<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\LandedCostRateType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LandedCostRateTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{LandedCostRateType, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'default_rate' => [LandedCostRateType::DefaultRate, 'default_rate'];
        yield 'derived_rate' => [LandedCostRateType::DerivedRate, 'derived_rate'];
        yield 'highest_rate' => [LandedCostRateType::HighestRate, 'highest_rate'];
        yield 'center_rate' => [LandedCostRateType::CenterRate, 'center_rate'];
        yield 'lowest_rate' => [LandedCostRateType::LowestRate, 'lowest_rate'];
        yield 'preferential_rate' => [LandedCostRateType::PreferentialRate, 'preferential_rate'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(LandedCostRateType $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllSixRateTypes(): void
    {
        self::assertCount(6, LandedCostRateType::cases());
    }
}

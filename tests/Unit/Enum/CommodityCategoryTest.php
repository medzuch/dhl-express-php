<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\CommodityCategory;
use PHPUnit\Framework\TestCase;

final class CommodityCategoryTest extends TestCase
{
    public function testIsIntBackedEnum(): void
    {
        $cases = CommodityCategory::cases();
        self::assertNotEmpty($cases);
        // Int-backed: each case value must be a positive integer
        self::assertGreaterThan(0, $cases[0]->value);
    }

    public function testCoatsAndJacketsHasCorrectValue(): void
    {
        self::assertSame(101, CommodityCategory::CoatsAndJackets->value);
    }

    public function testSportsEquipmentHasCorrectValue(): void
    {
        self::assertSame(1603, CommodityCategory::SportsEquipment->value);
    }

    public function testCaseCountIs108(): void
    {
        self::assertCount(108, CommodityCategory::cases());
    }

    public function testFromCanResolveByIntValue(): void
    {
        self::assertSame(CommodityCategory::Sneakers, CommodityCategory::from(201));
    }

    public function testHandSanitizerHasCorrectValue(): void
    {
        self::assertSame(1506, CommodityCategory::HandSanitizer->value);
    }
}

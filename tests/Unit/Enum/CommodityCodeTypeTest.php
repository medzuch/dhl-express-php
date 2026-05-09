<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\CommodityCodeType;
use PHPUnit\Framework\TestCase;

final class CommodityCodeTypeTest extends TestCase
{
    public function testExpectedCasesExist(): void
    {
        self::assertSame('outbound', CommodityCodeType::Outbound->value);
        self::assertSame('inbound', CommodityCodeType::Inbound->value);
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(CommodityCodeType::Outbound, CommodityCodeType::from('outbound'));
        self::assertSame(CommodityCodeType::Inbound, CommodityCodeType::from('inbound'));
    }
}

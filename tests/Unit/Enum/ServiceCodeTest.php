<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ServiceCode;
use PHPUnit\Framework\TestCase;

final class ServiceCodeTest extends TestCase
{
    public function testCaseCountIs383(): void
    {
        // 384 rows in the xlsx (excl. header), minus 1 for the duplicate TZ entry = 383 unique codes
        self::assertCount(383, ServiceCode::cases());
    }

    public function testPaperlessTradeWireValue(): void
    {
        self::assertSame('WY', ServiceCode::PaperlessTrade->value);
    }

    public function testShipmentInsuranceIIWireValue(): void
    {
        self::assertSame('II', ServiceCode::ShipmentInsuranceII->value);
    }

    public function testDryIceUN1845WireValue(): void
    {
        self::assertSame('HC', ServiceCode::DryIceUN1845->value);
    }

    public function testFromCanResolveByWireValue(): void
    {
        self::assertSame(ServiceCode::PaperlessTrade, ServiceCode::from('WY'));
    }

    public function testSaturdayDeliveryWireValue(): void
    {
        self::assertSame('AX', ServiceCode::SaturdayDelivery->value);
    }

    public function testIsStringBackedEnum(): void
    {
        $cases = ServiceCode::cases();
        // String-backed: each case value must be a non-empty string
        self::assertNotEmpty($cases[0]->value);
    }
}

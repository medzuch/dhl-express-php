<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ShipmentReferenceTypeCode;
use PHPUnit\Framework\TestCase;

final class ShipmentReferenceTypeCodeTest extends TestCase
{
    public function testCaseCountIs63(): void
    {
        self::assertCount(63, ShipmentReferenceTypeCode::cases());
    }

    public function testLcqHasCorrectWireValue(): void
    {
        self::assertSame('LCQ', ShipmentReferenceTypeCode::LCQ->value);
    }

    public function testMr1HasCorrectWireValue(): void
    {
        self::assertSame('MR1', ShipmentReferenceTypeCode::MR1->value);
    }

    public function testHwbHasCorrectWireValue(): void
    {
        self::assertSame('HWB', ShipmentReferenceTypeCode::HWB->value);
    }

    public function testFromCanResolveByWireValue(): void
    {
        self::assertSame(ShipmentReferenceTypeCode::BOL, ShipmentReferenceTypeCode::from('BOL'));
    }

    public function testIsStringBackedEnum(): void
    {
        $cases = ShipmentReferenceTypeCode::cases();
        // String-backed: each case value must be a non-empty string
        self::assertNotEmpty($cases[0]->value);
    }
}

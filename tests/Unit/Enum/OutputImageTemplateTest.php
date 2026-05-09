<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\OutputImageTemplate;
use PHPUnit\Framework\TestCase;

final class OutputImageTemplateTest extends TestCase
{
    public function testIsStringBackedEnum(): void
    {
        $cases = OutputImageTemplate::cases();
        self::assertNotEmpty($cases);
        // String-backed: each case value must be a non-empty string
        self::assertNotEmpty($cases[0]->value);
    }

    public function testEcom26_84_001HasCorrectWireValue(): void
    {
        self::assertSame('ECOM26_84_001', OutputImageTemplate::ECOM26_84_001->value);
    }

    public function testArch8x4HasCorrectWireValue(): void
    {
        self::assertSame('ARCH_8X4', OutputImageTemplate::ARCH_8X4->value);
    }

    public function testCommercialInvoiceP10HasCorrectWireValue(): void
    {
        self::assertSame('COMMERCIAL_INVOICE_P_10', OutputImageTemplate::COMMERCIAL_INVOICE_P_10->value);
    }

    public function testQrCode1HasCorrectWireValue(): void
    {
        self::assertSame('QR_1_00_LL_PNG_001', OutputImageTemplate::QR_1_00_LL_PNG_001->value);
    }

    public function testFromCanResolveByWireValue(): void
    {
        $template = OutputImageTemplate::from('ECOM26_84_001');
        self::assertSame(OutputImageTemplate::ECOM26_84_001, $template);
    }

    public function testCoversAll35Templates(): void
    {
        self::assertCount(35, OutputImageTemplate::cases());
    }
}

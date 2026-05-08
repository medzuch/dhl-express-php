<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\MerchantCarrier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MerchantCarrierTest extends TestCase
{
    /**
     * @return iterable<string, array{MerchantCarrier, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'DHL' => [MerchantCarrier::DHL, 'DHL'];
        yield 'UPS' => [MerchantCarrier::UPS, 'UPS'];
        yield 'FEDEX' => [MerchantCarrier::FEDEX, 'FEDEX'];
        yield 'TNT' => [MerchantCarrier::TNT, 'TNT'];
        yield 'POST' => [MerchantCarrier::POST, 'POST'];
        yield 'OTHERS' => [MerchantCarrier::Others, 'OTHERS'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(MerchantCarrier $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllSixCases(): void
    {
        self::assertCount(6, MerchantCarrier::cases());
    }
}

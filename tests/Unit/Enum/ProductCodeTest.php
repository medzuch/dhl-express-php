<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ProductCode;
use PHPUnit\Framework\TestCase;

final class ProductCodeTest extends TestCase
{
    public function testCoversAllThirtySixProductCodes(): void
    {
        self::assertCount(36, ProductCode::cases());
    }

    public function testRepresentativeWireCodesResolveToDescriptiveCases(): void
    {
        self::assertSame('D', ProductCode::ExpressWorldwideDox->value);
        self::assertSame('U', ProductCode::ExpressWorldwideEcx->value);
        self::assertSame('P', ProductCode::ExpressWorldwideWpx->value);
        self::assertSame('N', ProductCode::ExpressDomestic->value);
        self::assertSame('S', ProductCode::SameDay->value);
        self::assertSame('Z', ProductCode::DutiesAndTaxes->value);
    }

    public function testDigitWireCodesAreSupported(): void
    {
        self::assertSame('0', ProductCode::LogisticsServices->value);
        self::assertSame('9', ProductCode::ParcelProductDoc->value);
        self::assertSame(ProductCode::LogisticsServices, ProductCode::from('0'));
    }

    public function testFromCanResolveAlphaWireCode(): void
    {
        self::assertSame(ProductCode::ExpressWorldwideDox, ProductCode::from('D'));
    }

    public function testEachWireCodeIsExactlyOneCharacter(): void
    {
        foreach (ProductCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[0-9A-Z]$/', $case->value);
        }
    }
}

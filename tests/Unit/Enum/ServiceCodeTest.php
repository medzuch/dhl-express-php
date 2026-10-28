<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ServiceCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServiceCodeTest extends TestCase
{
    public function testCaseCountIs464(): void
    {
        // 464 unique rows in the `serviceCode` sheet of ExpressReferenceData_2026-10-25.xlsx
        self::assertCount(464, ServiceCode::cases());
    }

    public function testCodesAddedIn20261025WorkbookResolve(): void
    {
        self::assertSame(ServiceCode::GoGreenPlusCarbonReduced, ServiceCode::from('F1'));
        self::assertSame(ServiceCode::ActiveTemperatureControlledSystem, ServiceCode::from('5A'));
        self::assertSame(ServiceCode::AdditionalHandlingSurcharge, ServiceCode::from('YA'));
        self::assertSame(ServiceCode::NonConveyablePieceIrregular, ServiceCode::from('YL'));
        self::assertSame(ServiceCode::AmbientExtremeMF, ServiceCode::from('MF'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function retiredCodes(): iterable
    {
        foreach (['U7', 'U8', 'U9'] as $code) {
            yield $code => [$code];
        }
    }

    #[DataProvider('retiredCodes')]
    public function testRetiredAmbientShipperBoxCodesAreNotAccepted(string $code): void
    {
        self::assertNull(ServiceCode::tryFrom($code));
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

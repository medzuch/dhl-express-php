<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\UnitOfMeasurement;
use PHPUnit\Framework\TestCase;

final class UnitOfMeasurementTest extends TestCase
{
    public function testCoversAllFiftyNineUnits(): void
    {
        self::assertCount(59, UnitOfMeasurement::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('KG', UnitOfMeasurement::KG->value);
        self::assertSame('PCS', UnitOfMeasurement::PCS->value);
        self::assertSame('DOZ', UnitOfMeasurement::DOZ->value);
        self::assertSame('M3', UnitOfMeasurement::M3->value);
    }

    public function testDigitPrefixedWireCodesMapToDescriptiveCaseNames(): void
    {
        self::assertSame('2GM', UnitOfMeasurement::Centigram->value);
        self::assertSame('3GM', UnitOfMeasurement::Milligram->value);
        self::assertSame('2M2', UnitOfMeasurement::SquareFoot->value);
        self::assertSame('3M2', UnitOfMeasurement::SquareInch->value);
        self::assertSame('4M2', UnitOfMeasurement::SquareYard->value);
        self::assertSame(UnitOfMeasurement::Centigram, UnitOfMeasurement::from('2GM'));
    }

    public function testFromCanResolveAlphaWireCode(): void
    {
        self::assertSame(UnitOfMeasurement::KG, UnitOfMeasurement::from('KG'));
    }

    public function testEachWireCodeIsAtMostFourCharacters(): void
    {
        foreach (UnitOfMeasurement::cases() as $case) {
            self::assertMatchesRegularExpression('/^[0-9A-Z]{1,4}$/', $case->value);
        }
    }
}

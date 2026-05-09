<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use PHPUnit\Framework\TestCase;

final class LineItemQuantityUnitTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (LineItemQuantityUnit $case): string => $case->value, LineItemQuantityUnit::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testDigitStartingWireCodesHaveDescriptiveCaseNames(): void
    {
        // Cases whose wire code starts with a digit get PascalCase names
        self::assertSame('2GM', LineItemQuantityUnit::Centigram->value);
        self::assertSame('3GM', LineItemQuantityUnit::Milligram->value);
        self::assertSame('2M2', LineItemQuantityUnit::SquareFeet->value);
        self::assertSame('3M2', LineItemQuantityUnit::SquareInches->value);
        self::assertSame('4M2', LineItemQuantityUnit::SquareYards->value);
    }

    public function testAlphaStartingCasesKeepWireValue(): void
    {
        self::assertSame('BOX', LineItemQuantityUnit::BOX->value);
        self::assertSame('KG', LineItemQuantityUnit::KG->value);
        self::assertSame('LBS', LineItemQuantityUnit::LBS->value);
        self::assertSame('PCS', LineItemQuantityUnit::PCS->value);
        self::assertSame('M', LineItemQuantityUnit::M->value);
        self::assertSame('X', LineItemQuantityUnit::X->value);
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(LineItemQuantityUnit::Centigram, LineItemQuantityUnit::from('2GM'));
        self::assertSame(LineItemQuantityUnit::Milligram, LineItemQuantityUnit::from('3GM'));
        self::assertSame(LineItemQuantityUnit::SquareFeet, LineItemQuantityUnit::from('2M2'));
        self::assertSame(LineItemQuantityUnit::SquareInches, LineItemQuantityUnit::from('3M2'));
        self::assertSame(LineItemQuantityUnit::SquareYards, LineItemQuantityUnit::from('4M2'));
    }

    public function testExpectedCommonCasesExist(): void
    {
        $values = array_map(static fn (LineItemQuantityUnit $case): string => $case->value, LineItemQuantityUnit::cases());

        $expected = ['BOX', '2GM', 'M3', 'PCS', 'GM', 'KG', 'M', '3GM', 'X', 'NO', 'LBS', 'EA', 'SET'];

        foreach ($expected as $value) {
            self::assertContains($value, $values, "Missing case with value '{$value}'");
        }
    }
}

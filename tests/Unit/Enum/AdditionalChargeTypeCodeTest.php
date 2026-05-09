<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\AdditionalChargeTypeCode;
use PHPUnit\Framework\TestCase;

final class AdditionalChargeTypeCodeTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (AdditionalChargeTypeCode $case): string => $case->value, AdditionalChargeTypeCode::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testExpectedCasesExist(): void
    {
        $cases = AdditionalChargeTypeCode::cases();
        $values = array_map(static fn (AdditionalChargeTypeCode $case): string => $case->value, $cases);

        $expected = [
            'admin',
            'delivery',
            'documentation',
            'expedite',
            'export',
            'freight',
            'fuel_surcharge',
            'logistic',
            'other',
            'packaging',
            'pickup',
            'handling',
            'vat',
            'insurance',
            'reverse_charge',
        ];

        foreach ($expected as $value) {
            self::assertContains($value, $values, "Missing case with value '{$value}'");
        }
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(AdditionalChargeTypeCode::Admin, AdditionalChargeTypeCode::from('admin'));
        self::assertSame(AdditionalChargeTypeCode::Insurance, AdditionalChargeTypeCode::from('insurance'));
        self::assertSame(AdditionalChargeTypeCode::FuelSurcharge, AdditionalChargeTypeCode::from('fuel_surcharge'));
    }
}

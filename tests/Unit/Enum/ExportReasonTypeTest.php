<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ExportReasonType;
use PHPUnit\Framework\TestCase;

final class ExportReasonTypeTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (ExportReasonType $case): string => $case->value, ExportReasonType::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(ExportReasonType::Permanent, ExportReasonType::from('permanent'));
        self::assertSame(ExportReasonType::Gift, ExportReasonType::from('gift'));
        self::assertSame(ExportReasonType::Sample, ExportReasonType::from('sample'));
        self::assertSame(ExportReasonType::ReturnToOrigin, ExportReasonType::from('return_to_origin'));
        self::assertSame(ExportReasonType::WarrantyReplacement, ExportReasonType::from('warranty_replacement'));
    }

    public function testExpectedCasesExist(): void
    {
        $cases = ExportReasonType::cases();
        $values = array_map(static fn (ExportReasonType $case): string => $case->value, $cases);

        $expected = [
            'permanent',
            'temporary',
            'return',
            'used_exhibition_goods_to_origin',
            'intercompany_use',
            'commercial_purpose_or_sale',
            'personal_belongings_or_personal_use',
            'sample',
            'gift',
            'return_to_origin',
            'warranty_replacement',
            'diplomatic_goods',
            'defence_material',
        ];

        foreach ($expected as $value) {
            self::assertContains($value, $values, "Missing case with value '{$value}'");
        }
    }
}

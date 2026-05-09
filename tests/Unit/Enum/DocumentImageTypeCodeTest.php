<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DocumentImageTypeCode;
use PHPUnit\Framework\TestCase;

final class DocumentImageTypeCodeTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (DocumentImageTypeCode $case): string => $case->value, DocumentImageTypeCode::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testExpectedCasesExist(): void
    {
        $values = array_map(static fn (DocumentImageTypeCode $case): string => $case->value, DocumentImageTypeCode::cases());

        foreach (['INV', 'PNV', 'COO', 'NAF', 'CIN', 'DCL', 'AWB'] as $expected) {
            self::assertContains($expected, $values, "Missing case with value '{$expected}'");
        }
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(DocumentImageTypeCode::INV, DocumentImageTypeCode::from('INV'));
        self::assertSame(DocumentImageTypeCode::AWB, DocumentImageTypeCode::from('AWB'));
        self::assertSame(DocumentImageTypeCode::COO, DocumentImageTypeCode::from('COO'));
    }
}

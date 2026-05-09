<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DocumentImageFormat;
use PHPUnit\Framework\TestCase;

final class DocumentImageFormatTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (DocumentImageFormat $case): string => $case->value, DocumentImageFormat::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testExpectedCasesExist(): void
    {
        $values = array_map(static fn (DocumentImageFormat $case): string => $case->value, DocumentImageFormat::cases());

        foreach (['PDF', 'PNG', 'GIF', 'TIFF', 'JPEG'] as $expected) {
            self::assertContains($expected, $values, "Missing case with value '{$expected}'");
        }
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(DocumentImageFormat::PDF, DocumentImageFormat::from('PDF'));
        self::assertSame(DocumentImageFormat::JPEG, DocumentImageFormat::from('JPEG'));
        self::assertSame(DocumentImageFormat::TIFF, DocumentImageFormat::from('TIFF'));
    }
}

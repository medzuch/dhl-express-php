<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\GetImageEncodingFormat;
use PHPUnit\Framework\TestCase;

final class GetImageEncodingFormatTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (GetImageEncodingFormat $case): string => $case->value, GetImageEncodingFormat::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testOnlyPdfAndTiffCasesExist(): void
    {
        $values = array_map(static fn (GetImageEncodingFormat $case): string => $case->value, GetImageEncodingFormat::cases());

        self::assertEqualsCanonicalizing(['pdf', 'tiff'], $values);
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(GetImageEncodingFormat::Pdf, GetImageEncodingFormat::from('pdf'));
        self::assertSame(GetImageEncodingFormat::Tiff, GetImageEncodingFormat::from('tiff'));
    }
}

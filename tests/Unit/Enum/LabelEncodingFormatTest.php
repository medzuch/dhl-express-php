<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LabelEncodingFormatTest extends TestCase
{
    /**
     * @return iterable<string, array{LabelEncodingFormat, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'pdf' => [LabelEncodingFormat::Pdf, 'pdf'];
        yield 'zpl' => [LabelEncodingFormat::Zpl, 'zpl'];
        yield 'lp2' => [LabelEncodingFormat::Lp2, 'lp2'];
        yield 'epl' => [LabelEncodingFormat::Epl, 'epl'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(LabelEncodingFormat $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversFourCases(): void
    {
        self::assertCount(4, LabelEncodingFormat::cases());
    }
}

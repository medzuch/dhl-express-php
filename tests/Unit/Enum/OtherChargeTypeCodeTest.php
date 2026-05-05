<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\OtherChargeTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OtherChargeTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{OtherChargeTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'ADMIN' => [OtherChargeTypeCode::ADMIN, 'ADMIN'];
        yield 'DELIV' => [OtherChargeTypeCode::DELIV, 'DELIV'];
        yield 'DOCUM' => [OtherChargeTypeCode::DOCUM, 'DOCUM'];
        yield 'EXPED' => [OtherChargeTypeCode::EXPED, 'EXPED'];
        yield 'EXCHA' => [OtherChargeTypeCode::EXCHA, 'EXCHA'];
        yield 'FRCST' => [OtherChargeTypeCode::FRCST, 'FRCST'];
        yield 'SSRGE' => [OtherChargeTypeCode::SSRGE, 'SSRGE'];
        yield 'LOGST' => [OtherChargeTypeCode::LOGST, 'LOGST'];
        yield 'SOTHR' => [OtherChargeTypeCode::SOTHR, 'SOTHR'];
        yield 'SPKGN' => [OtherChargeTypeCode::SPKGN, 'SPKGN'];
        yield 'PICUP' => [OtherChargeTypeCode::PICUP, 'PICUP'];
        yield 'HRCRG' => [OtherChargeTypeCode::HRCRG, 'HRCRG'];
        yield 'VATCR' => [OtherChargeTypeCode::VATCR, 'VATCR'];
        yield 'INSCH' => [OtherChargeTypeCode::INSCH, 'INSCH'];
        yield 'REVCH' => [OtherChargeTypeCode::REVCH, 'REVCH'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(OtherChargeTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllFifteenChargeTypes(): void
    {
        self::assertCount(15, OtherChargeTypeCode::cases());
    }
}

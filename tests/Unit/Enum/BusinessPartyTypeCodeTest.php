<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\BusinessPartyTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BusinessPartyTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{BusinessPartyTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'BU' => [BusinessPartyTypeCode::BU, 'BU'];
        yield 'DC' => [BusinessPartyTypeCode::DC, 'DC'];
        yield 'GV' => [BusinessPartyTypeCode::GV, 'GV'];
        yield 'OT' => [BusinessPartyTypeCode::OT, 'OT'];
        yield 'PR' => [BusinessPartyTypeCode::PR, 'PR'];
        yield 'RE' => [BusinessPartyTypeCode::RE, 'RE'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(BusinessPartyTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllSixBusinessPartyTypes(): void
    {
        self::assertCount(6, BusinessPartyTypeCode::cases());
    }
}

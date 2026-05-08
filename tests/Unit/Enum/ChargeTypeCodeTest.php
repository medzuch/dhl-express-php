<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ChargeTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ChargeTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{ChargeTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'freight' => [ChargeTypeCode::Freight, 'freight'];
        yield 'additional' => [ChargeTypeCode::Additional, 'additional'];
        yield 'insurance' => [ChargeTypeCode::Insurance, 'insurance'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(ChargeTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllThreeCases(): void
    {
        self::assertCount(3, ChargeTypeCode::cases());
    }
}

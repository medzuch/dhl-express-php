<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\PackageTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PackageTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{PackageTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'TBS' => [PackageTypeCode::TBS, 'TBS'];
        yield 'TBL' => [PackageTypeCode::TBL, 'TBL'];
        yield '1CE' => [PackageTypeCode::CardEnvelopeMetric, '1CE'];
        yield 'CE1' => [PackageTypeCode::CardEnvelopeImperial, 'CE1'];
        yield '2BC' => [PackageTypeCode::Box2Cube, '2BC'];
        yield 'XPD' => [PackageTypeCode::XPD, 'XPD'];
        yield '2BP' => [PackageTypeCode::Box2Pizza, '2BP'];
        yield 'WB1' => [PackageTypeCode::WB1, 'WB1'];
        yield 'WB2' => [PackageTypeCode::WB2, 'WB2'];
        yield 'WB3' => [PackageTypeCode::WB3, 'WB3'];
        yield 'WB6' => [PackageTypeCode::WB6, 'WB6'];
        yield '2BX' => [PackageTypeCode::Box2Shoe, '2BX'];
        yield '3BX' => [PackageTypeCode::Box3, '3BX'];
        yield '4BX' => [PackageTypeCode::Box4, '4BX'];
        yield '5BX' => [PackageTypeCode::Box5JumboSmall, '5BX'];
        yield '6BX' => [PackageTypeCode::Box6, '6BX'];
        yield '7BX' => [PackageTypeCode::Box7, '7BX'];
        yield '8BX' => [PackageTypeCode::Box8JumboLarge, '8BX'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(PackageTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllEighteenGlobalPackageTypes(): void
    {
        self::assertCount(18, PackageTypeCode::cases());
    }

    public function testFromCanResolveByDhlCode(): void
    {
        self::assertSame(PackageTypeCode::TBS, PackageTypeCode::from('TBS'));
    }
}

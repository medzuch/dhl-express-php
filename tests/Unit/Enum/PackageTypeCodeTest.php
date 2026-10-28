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
        yield 'BB1' => [PackageTypeCode::BB1, 'BB1'];
        yield 'BB2' => [PackageTypeCode::BB2, 'BB2'];
        yield 'BB3' => [PackageTypeCode::BB3, 'BB3'];
        yield 'BB6' => [PackageTypeCode::BB6, 'BB6'];
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

    /**
     * @return iterable<string, array{string}>
     */
    public static function retiredCodes(): iterable
    {
        foreach (['WB1', 'WB2', 'WB3', 'WB6'] as $code) {
            yield $code => [$code];
        }
    }

    #[DataProvider('retiredCodes')]
    public function testRetiredWineBoxCodesAreNotAccepted(string $code): void
    {
        self::assertNull(PackageTypeCode::tryFrom($code));
    }

    public function testFromCanResolveByDhlCode(): void
    {
        self::assertSame(PackageTypeCode::TBS, PackageTypeCode::from('TBS'));
    }
}

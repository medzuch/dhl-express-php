<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\PackageReferenceTypeCode;
use PHPUnit\Framework\TestCase;

final class PackageReferenceTypeCodeTest extends TestCase
{
    public function testCoversEightyOnePackageReferenceCodes(): void
    {
        self::assertCount(81, PackageReferenceTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('CU', PackageReferenceTypeCode::CU->value);
        self::assertSame('AAO', PackageReferenceTypeCode::AAO->value);
        self::assertSame('CDN', PackageReferenceTypeCode::CDN->value);
        self::assertSame('PRN', PackageReferenceTypeCode::PRN->value);
        self::assertSame('MRN', PackageReferenceTypeCode::MRN->value);
        self::assertSame('HWB', PackageReferenceTypeCode::HWB->value);
        self::assertSame('WLK', PackageReferenceTypeCode::WLK->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(PackageReferenceTypeCode::CU, PackageReferenceTypeCode::from('CU'));
    }

    public function testEachBackingValueIsTwoToFourUppercaseLetters(): void
    {
        foreach (PackageReferenceTypeCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[A-Z]{2,4}$/', $case->value);
        }
    }
}

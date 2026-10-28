<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\PackageReferenceTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PackageReferenceTypeCodeTest extends TestCase
{
    public function testCoversFortyPackageReferenceCodes(): void
    {
        self::assertCount(40, PackageReferenceTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('CU', PackageReferenceTypeCode::CU->value);
        self::assertSame('AAJ', PackageReferenceTypeCode::AAJ->value);
        self::assertSame('MRN', PackageReferenceTypeCode::MRN->value);
        self::assertSame('WLK', PackageReferenceTypeCode::WLK->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function retiredCodes(): iterable
    {
        foreach (['AAO', 'CDN', 'PRN', 'HWB', 'IME', 'IPP'] as $code) {
            yield $code => [$code];
        }
    }

    #[DataProvider('retiredCodes')]
    public function testRetiredCodesAreNotAccepted(string $code): void
    {
        self::assertNull(PackageReferenceTypeCode::tryFrom($code));
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

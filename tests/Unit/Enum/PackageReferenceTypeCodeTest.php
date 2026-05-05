<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\PackageReferenceTypeCode;
use PHPUnit\Framework\TestCase;

final class PackageReferenceTypeCodeTest extends TestCase
{
    public function testCoversFourteenPackageReferenceCodes(): void
    {
        self::assertCount(14, PackageReferenceTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('CU', PackageReferenceTypeCode::CU->value);
        self::assertSame('AAO', PackageReferenceTypeCode::AAO->value);
        self::assertSame('CDN', PackageReferenceTypeCode::CDN->value);
        self::assertSame('PRN', PackageReferenceTypeCode::PRN->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(PackageReferenceTypeCode::CU, PackageReferenceTypeCode::from('CU'));
    }
}

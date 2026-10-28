<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\CustomsDocumentTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CustomsDocumentTypeCodeTest extends TestCase
{
    public function testCoversFiftyThreeUniqueCustomsDocumentCodes(): void
    {
        self::assertCount(53, CustomsDocumentTypeCode::cases());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function retiredCodes(): iterable
    {
        foreach (['CHA', 'POA'] as $code) {
            yield $code => [$code];
        }
    }

    #[DataProvider('retiredCodes')]
    public function testRetiredCodesAreNotAccepted(string $code): void
    {
        self::assertNull(CustomsDocumentTypeCode::tryFrom($code));
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('INV', CustomsDocumentTypeCode::INV->value);
        self::assertSame('COO', CustomsDocumentTypeCode::COO->value);
        self::assertSame('PAS', CustomsDocumentTypeCode::PAS->value);
        self::assertSame('DGD', CustomsDocumentTypeCode::DGD->value);
        self::assertSame('IMP', CustomsDocumentTypeCode::IMP->value);
        self::assertSame('PPY', CustomsDocumentTypeCode::PPY->value);
    }

    public function testDigitPrefixedWireCodeMapsToDescriptiveCaseName(): void
    {
        self::assertSame('972', CustomsDocumentTypeCode::T2LFDispense->value);
        self::assertSame(CustomsDocumentTypeCode::T2LFDispense, CustomsDocumentTypeCode::from('972'));
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(CustomsDocumentTypeCode::INV, CustomsDocumentTypeCode::from('INV'));
    }
}

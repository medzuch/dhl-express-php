<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ShipmentReferenceTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShipmentReferenceTypeCodeTest extends TestCase
{
    public function testCaseCountMatchesWorkbook(): void
    {
        self::assertCount(16, ShipmentReferenceTypeCode::cases());
    }

    public function testLcqHasCorrectWireValue(): void
    {
        self::assertSame('LCQ', ShipmentReferenceTypeCode::LCQ->value);
    }

    public function testAfmHasCorrectWireValue(): void
    {
        self::assertSame('AFM', ShipmentReferenceTypeCode::AFM->value);
    }

    public function testFromCanResolveByWireValue(): void
    {
        self::assertSame(ShipmentReferenceTypeCode::UCN, ShipmentReferenceTypeCode::from('UCN'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function retiredCodes(): iterable
    {
        foreach (['AAO', 'BOL', 'HWB', 'MR1', 'BOE', 'PBE'] as $code) {
            yield $code => [$code];
        }
    }

    #[DataProvider('retiredCodes')]
    public function testRetiredCodesAreNotAccepted(string $code): void
    {
        self::assertNull(ShipmentReferenceTypeCode::tryFrom($code));
    }

    public function testIsStringBackedEnum(): void
    {
        $cases = ShipmentReferenceTypeCode::cases();
        // String-backed: each case value must be a non-empty string
        self::assertNotEmpty($cases[0]->value);
    }
}

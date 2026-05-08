<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ShipmentPurpose;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShipmentPurposeTest extends TestCase
{
    /**
     * @return iterable<string, array{ShipmentPurpose, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'commercial' => [ShipmentPurpose::Commercial, 'commercial'];
        yield 'personal' => [ShipmentPurpose::Personal, 'personal'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(ShipmentPurpose $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversBothCases(): void
    {
        self::assertCount(2, ShipmentPurpose::cases());
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EstimatedDeliveryDateTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{EstimatedDeliveryDateTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'QDDC' => [EstimatedDeliveryDateTypeCode::QDDC, 'QDDC'];
        yield 'QDDF' => [EstimatedDeliveryDateTypeCode::QDDF, 'QDDF'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(EstimatedDeliveryDateTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversBothCases(): void
    {
        self::assertCount(2, EstimatedDeliveryDateTypeCode::cases());
    }
}

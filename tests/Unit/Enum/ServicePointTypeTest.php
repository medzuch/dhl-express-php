<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ServicePointType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServicePointTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{ServicePointType, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'CITY' => [ServicePointType::City, 'CITY'];
        yield 'STATION' => [ServicePointType::Station, 'STATION'];
        yield 'PARTNER' => [ServicePointType::Partner, 'PARTNER'];
        yield 'TWENTYFOURSEVEN' => [ServicePointType::TwentyFourSeven, 'TWENTYFOURSEVEN'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(ServicePointType $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversFourCases(): void
    {
        self::assertCount(4, ServicePointType::cases());
    }
}

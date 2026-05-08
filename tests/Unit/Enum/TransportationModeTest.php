<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\TransportationMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TransportationModeTest extends TestCase
{
    /**
     * @return iterable<string, array{TransportationMode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'air' => [TransportationMode::Air, 'air'];
        yield 'ocean' => [TransportationMode::Ocean, 'ocean'];
        yield 'ground' => [TransportationMode::Ground, 'ground'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(TransportationMode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllThreeCases(): void
    {
        self::assertCount(3, TransportationMode::cases());
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\Incoterm;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IncotermTest extends TestCase
{
    /**
     * @return iterable<string, array{Incoterm, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'EXW' => [Incoterm::EXW, 'EXW'];
        yield 'FCA' => [Incoterm::FCA, 'FCA'];
        yield 'CPT' => [Incoterm::CPT, 'CPT'];
        yield 'CIP' => [Incoterm::CIP, 'CIP'];
        yield 'DPU' => [Incoterm::DPU, 'DPU'];
        yield 'DAP' => [Incoterm::DAP, 'DAP'];
        yield 'DDP' => [Incoterm::DDP, 'DDP'];
        yield 'FAS' => [Incoterm::FAS, 'FAS'];
        yield 'FOB' => [Incoterm::FOB, 'FOB'];
        yield 'CFR' => [Incoterm::CFR, 'CFR'];
        yield 'CIF' => [Incoterm::CIF, 'CIF'];
        yield 'DAF' => [Incoterm::DAF, 'DAF'];
        yield 'DAT' => [Incoterm::DAT, 'DAT'];
        yield 'DDU' => [Incoterm::DDU, 'DDU'];
        yield 'DEQ' => [Incoterm::DEQ, 'DEQ'];
        yield 'DES' => [Incoterm::DES, 'DES'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(Incoterm $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversSixteenIncoterms(): void
    {
        self::assertCount(16, Incoterm::cases());
    }

    public function testFromCanResolveByDhlCode(): void
    {
        self::assertSame(Incoterm::DAP, Incoterm::from('DAP'));
    }
}

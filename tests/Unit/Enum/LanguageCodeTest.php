<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\LanguageCode;
use PHPUnit\Framework\TestCase;

final class LanguageCodeTest extends TestCase
{
    public function testCoversAllFortySixLanguages(): void
    {
        self::assertCount(46, LanguageCode::cases());
    }

    public function testDhlDefaultLanguageIsEnglish(): void
    {
        self::assertSame('eng', LanguageCode::Eng->value);
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('cze', LanguageCode::Cze->value);
        self::assertSame('ger', LanguageCode::Ger->value);
        self::assertSame('jpn', LanguageCode::Jpn->value);
        self::assertSame('chi', LanguageCode::Chi->value);
        self::assertSame('zho', LanguageCode::Zho->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(LanguageCode::Eng, LanguageCode::from('eng'));
    }

    public function testEachBackingValueIsThreeLowercaseLetters(): void
    {
        foreach (LanguageCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[a-z]{3}$/', $case->value);
        }
    }
}

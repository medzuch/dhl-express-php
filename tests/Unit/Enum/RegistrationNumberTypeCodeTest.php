<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\RegistrationNumberTypeCode;
use PHPUnit\Framework\TestCase;

final class RegistrationNumberTypeCodeTest extends TestCase
{
    public function testCoversTwentyNineRegistrationCodes(): void
    {
        self::assertCount(29, RegistrationNumberTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('VAT', RegistrationNumberTypeCode::VAT->value);
        self::assertSame('EIN', RegistrationNumberTypeCode::EIN->value);
        self::assertSame('EOR', RegistrationNumberTypeCode::EOR->value);
        self::assertSame('CNP', RegistrationNumberTypeCode::CNP->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(RegistrationNumberTypeCode::VAT, RegistrationNumberTypeCode::from('VAT'));
    }

    public function testEachBackingValueIsTwoToThreeUppercaseLetters(): void
    {
        foreach (RegistrationNumberTypeCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[A-Z]{2,3}$/', $case->value);
        }
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\RegistrationNumberTypeCode;
use PHPUnit\Framework\TestCase;

final class RegistrationNumberTypeCodeTest extends TestCase
{
    public function testCoversTwentyFiveRegistrationCodes(): void
    {
        self::assertCount(25, RegistrationNumberTypeCode::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('VAT', RegistrationNumberTypeCode::VAT->value);
        self::assertSame('EIN', RegistrationNumberTypeCode::EIN->value);
        self::assertSame('EOR', RegistrationNumberTypeCode::EOR->value);
        self::assertSame('CNP', RegistrationNumberTypeCode::CNP->value);
        self::assertSame('DUT', RegistrationNumberTypeCode::DUT->value);
        self::assertSame('SUB', RegistrationNumberTypeCode::SUB->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(RegistrationNumberTypeCode::VAT, RegistrationNumberTypeCode::from('VAT'));
    }

    public function testEachBackingValueIsThreeUppercaseLetters(): void
    {
        foreach (RegistrationNumberTypeCode::cases() as $case) {
            self::assertMatchesRegularExpression('/^[A-Z]{3}$/', $case->value);
        }
    }
}

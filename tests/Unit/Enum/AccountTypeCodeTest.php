<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\AccountTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AccountTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{AccountTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'shipper' => [AccountTypeCode::Shipper, 'shipper'];
        yield 'payer' => [AccountTypeCode::Payer, 'payer'];
        yield 'duties-taxes' => [AccountTypeCode::DutiesTaxes, 'duties-taxes'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(AccountTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllThreeTypes(): void
    {
        self::assertCount(3, AccountTypeCode::cases());
    }
}

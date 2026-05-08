<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Common;

use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    public function testToArrayMatchesDhlWireShape(): void
    {
        $account = new Account(
            typeCode: AccountTypeCode::Shipper,
            number: new AccountNumber('123456789'),
        );

        self::assertSame(
            ['typeCode' => 'shipper', 'number' => '123456789'],
            $account->toArray(),
        );
    }

    public function testDutiesTaxesUsesHyphenatedWireValue(): void
    {
        $account = new Account(
            typeCode: AccountTypeCode::DutiesTaxes,
            number: new AccountNumber('999000111'),
        );

        self::assertSame('duties-taxes', $account->toArray()['typeCode']);
    }
}

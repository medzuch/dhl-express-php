<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\AddressValidationType;
use PHPUnit\Framework\TestCase;

final class AddressValidationTypeTest extends TestCase
{
    public function testCoversTwoValidationTypes(): void
    {
        self::assertCount(2, AddressValidationType::cases());
    }

    public function testCasesResolveToTheirBackingValues(): void
    {
        self::assertSame('pickup', AddressValidationType::Pickup->value);
        self::assertSame('delivery', AddressValidationType::Delivery->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(AddressValidationType::Pickup, AddressValidationType::from('pickup'));
    }
}

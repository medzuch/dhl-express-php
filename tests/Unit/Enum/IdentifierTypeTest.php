<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\IdentifierType;
use PHPUnit\Framework\TestCase;

final class IdentifierTypeTest extends TestCase
{
    public function testCoversSevenIdentifierTypes(): void
    {
        self::assertCount(7, IdentifierType::cases());
    }

    public function testRepresentativeCodesResolveToTheirBackingValues(): void
    {
        self::assertSame('SID', IdentifierType::SID->value);
        self::assertSame('PID', IdentifierType::PID->value);
        self::assertSame('ASID3', IdentifierType::ASID3->value);
        self::assertSame('ASID24', IdentifierType::ASID24->value);
        self::assertSame('HUID', IdentifierType::HUID->value);
    }

    public function testFromCanResolveKnownCode(): void
    {
        self::assertSame(IdentifierType::SID, IdentifierType::from('SID'));
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ValueAddedServiceMethod;
use PHPUnit\Framework\TestCase;

final class ValueAddedServiceMethodTest extends TestCase
{
    public function testCashBackingValueMatchesDhlCode(): void
    {
        self::assertSame('cash', ValueAddedServiceMethod::Cash->value);
    }

    public function testFromResolvesCash(): void
    {
        self::assertSame(ValueAddedServiceMethod::Cash, ValueAddedServiceMethod::from('cash'));
    }

    public function testCoversAllSpecCases(): void
    {
        self::assertCount(1, ValueAddedServiceMethod::cases());
    }
}

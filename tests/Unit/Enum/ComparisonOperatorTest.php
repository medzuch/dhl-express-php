<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ComparisonOperator;
use PHPUnit\Framework\TestCase;

final class ComparisonOperatorTest extends TestCase
{
    public function testCoversThreeOperators(): void
    {
        self::assertCount(3, ComparisonOperator::cases());
    }

    public function testCasesResolveToTheirBackingValues(): void
    {
        self::assertSame('equal', ComparisonOperator::Equal->value);
        self::assertSame('notEqual', ComparisonOperator::NotEqual->value);
        self::assertSame('contains', ComparisonOperator::Contains->value);
    }

    public function testFromCanResolveKnownOperator(): void
    {
        self::assertSame(ComparisonOperator::Equal, ComparisonOperator::from('equal'));
    }
}

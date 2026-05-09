<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Support;

use Medzuch\DhlExpress\Support\HydrationHelper;
use PHPUnit\Framework\TestCase;

final class HydrationHelperTest extends TestCase
{
    public function testStringFieldReturnsValueWhenString(): void
    {
        self::assertSame('hello', HydrationHelper::stringField(['key' => 'hello'], 'key'));
    }

    public function testStringFieldReturnsEmptyWhenMissing(): void
    {
        self::assertSame('', HydrationHelper::stringField([], 'key'));
    }

    public function testStringFieldReturnsEmptyWhenNonString(): void
    {
        self::assertSame('', HydrationHelper::stringField(['key' => 42], 'key'));
        self::assertSame('', HydrationHelper::stringField(['key' => null], 'key'));
        self::assertSame('', HydrationHelper::stringField(['key' => ['nested']], 'key'));
    }

    public function testNullableStringFieldReturnsNullWhenMissing(): void
    {
        self::assertNull(HydrationHelper::nullableStringField([], 'key'));
    }

    public function testNullableStringFieldReturnsValueWhenString(): void
    {
        self::assertSame('hi', HydrationHelper::nullableStringField(['key' => 'hi'], 'key'));
    }

    public function testNullableStringFieldReturnsNullWhenNonString(): void
    {
        self::assertNull(HydrationHelper::nullableStringField(['key' => 1], 'key'));
        self::assertNull(HydrationHelper::nullableStringField(['key' => false], 'key'));
    }

    public function testNullableBoolFieldReturnsValueWhenBool(): void
    {
        self::assertTrue(HydrationHelper::nullableBoolField(['key' => true], 'key'));
        self::assertFalse(HydrationHelper::nullableBoolField(['key' => false], 'key'));
    }

    public function testNullableBoolFieldReturnsNullWhenMissingOrCoerced(): void
    {
        self::assertNull(HydrationHelper::nullableBoolField([], 'key'));
        self::assertNull(HydrationHelper::nullableBoolField(['key' => 1], 'key'));
        self::assertNull(HydrationHelper::nullableBoolField(['key' => 'true'], 'key'));
    }

    public function testFloatFieldHandlesIntFloatAndNumericString(): void
    {
        self::assertSame(42.0, HydrationHelper::floatField(['key' => 42], 'key'));
        self::assertSame(1.5, HydrationHelper::floatField(['key' => 1.5], 'key'));
        self::assertSame(2.5, HydrationHelper::floatField(['key' => '2.5'], 'key'));
    }

    public function testFloatFieldReturnsNullForNonNumeric(): void
    {
        self::assertNull(HydrationHelper::floatField([], 'key'));
        self::assertNull(HydrationHelper::floatField(['key' => 'abc'], 'key'));
        self::assertNull(HydrationHelper::floatField(['key' => null], 'key'));
        self::assertNull(HydrationHelper::floatField(['key' => true], 'key'));
    }

    public function testIntFieldHandlesIntFloatAndNumericString(): void
    {
        self::assertSame(42, HydrationHelper::intField(['key' => 42], 'key'));
        self::assertSame(1, HydrationHelper::intField(['key' => 1.9], 'key'));
        self::assertSame(7, HydrationHelper::intField(['key' => '7'], 'key'));
    }

    public function testIntFieldReturnsNullForNonNumeric(): void
    {
        self::assertNull(HydrationHelper::intField([], 'key'));
        self::assertNull(HydrationHelper::intField(['key' => 'abc'], 'key'));
        self::assertNull(HydrationHelper::intField(['key' => null], 'key'));
    }

    public function testStringListFiltersNonStrings(): void
    {
        self::assertSame(['a', 'b'], HydrationHelper::stringList(['a', 1, 'b', null, true]));
    }

    public function testStringListReturnsEmptyForNonArray(): void
    {
        self::assertSame([], HydrationHelper::stringList(null));
        self::assertSame([], HydrationHelper::stringList('not an array'));
        self::assertSame([], HydrationHelper::stringList(42));
    }

    public function testStringListReturnsEmptyForEmptyArray(): void
    {
        self::assertSame([], HydrationHelper::stringList([]));
    }
}

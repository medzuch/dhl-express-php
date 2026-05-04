<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use PHPUnit\Framework\TestCase;

final class MessageReferenceTest extends TestCase
{
    public function testExposesValue(): void
    {
        $reference = new MessageReference('33a5611c-785e-4795-8a96-b9920976072f');

        self::assertSame('33a5611c-785e-4795-8a96-b9920976072f', $reference->value);
    }

    public function testCastsToStringYieldsTheRawValue(): void
    {
        $reference = new MessageReference('abc-123');

        self::assertSame('abc-123', (string) $reference);
    }

    public function testAcceptsExactlyThirtySixCharacters(): void
    {
        $value = str_repeat('a', 36);

        $reference = new MessageReference($value);

        self::assertSame($value, $reference->value);
    }

    public function testAcceptsSingleCharacter(): void
    {
        $reference = new MessageReference('x');

        self::assertSame('x', $reference->value);
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message reference must be 1-36 characters');

        new MessageReference('');
    }

    public function testRejectsValueLongerThanThirtySixCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Message reference must be 1-36 characters');

        new MessageReference(str_repeat('a', 37));
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $a = new MessageReference('abc');
        $b = new MessageReference('abc');

        self::assertTrue($a->equals($b));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $a = new MessageReference('abc');
        $b = new MessageReference('xyz');

        self::assertFalse($a->equals($b));
    }
}

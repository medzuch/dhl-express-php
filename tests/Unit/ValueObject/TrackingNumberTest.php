<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use PHPUnit\Framework\TestCase;

final class TrackingNumberTest extends TestCase
{
    public function testExposesValue(): void
    {
        $number = new TrackingNumber('9356579890');

        self::assertSame('9356579890', $number->value);
    }

    public function testCastsToStringYieldsTheRawValue(): void
    {
        $number = new TrackingNumber('9356579890');

        self::assertSame('9356579890', (string) $number);
    }

    public function testAcceptsTenDigitNumericFromDhlTestSet(): void
    {
        $number = new TrackingNumber('4818240420');

        self::assertSame('4818240420', $number->value);
    }

    public function testAcceptsAlphanumericPieceTrackingNumber(): void
    {
        $number = new TrackingNumber('JD012345678901234567');

        self::assertSame('JD012345678901234567', $number->value);
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tracking number must not be empty');

        new TrackingNumber('');
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $a = new TrackingNumber('9356579890');
        $b = new TrackingNumber('9356579890');

        self::assertTrue($a->equals($b));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $a = new TrackingNumber('9356579890');
        $b = new TrackingNumber('4818240420');

        self::assertFalse($a->equals($b));
    }
}

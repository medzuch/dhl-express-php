<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\ValueObject;

use InvalidArgumentException;
use Medzuch\DhlExpress\ValueObject\PlatformIdentifier;
use PHPUnit\Framework\TestCase;

final class PlatformIdentifierTest extends TestCase
{
    public function testExposesNameAndVersion(): void
    {
        $identifier = new PlatformIdentifier(name: 'AcmeShipper', version: '2.4.1');

        self::assertSame('AcmeShipper', $identifier->name);
        self::assertSame('2.4.1', $identifier->version);
    }

    public function testAcceptsTwentyCharNameAndFifteenCharVersion(): void
    {
        $identifier = new PlatformIdentifier(
            name: str_repeat('a', 20),
            version: str_repeat('1', 15),
        );

        self::assertSame(str_repeat('a', 20), $identifier->name);
        self::assertSame(str_repeat('1', 15), $identifier->version);
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Platform name must not be empty');

        new PlatformIdentifier(name: '', version: '1.0');
    }

    public function testRejectsEmptyVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Platform version must not be empty');

        new PlatformIdentifier(name: 'AcmeShipper', version: '');
    }

    public function testRejectsNameLongerThanTwentyChars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Platform name must be at most 20 characters');

        new PlatformIdentifier(name: str_repeat('a', 21), version: '1.0');
    }

    public function testRejectsVersionLongerThanFifteenChars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Platform version must be at most 15 characters');

        new PlatformIdentifier(name: 'AcmeShipper', version: str_repeat('1', 16));
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use InvalidArgumentException;
use Medzuch\DhlExpress\Dto\Shipment\DeclarationNote;
use PHPUnit\Framework\TestCase;

final class DeclarationNoteTest extends TestCase
{
    public function testSerializesAsValueObject(): void
    {
        self::assertSame(['value' => 'Note'], (new DeclarationNote('Note'))->toArray());
    }

    public function testAcceptsSevenHundredCharacters(): void
    {
        self::assertSame(700, strlen((new DeclarationNote(str_repeat('a', 700)))->value));
    }

    public function testRejectsMoreThanSevenHundredCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('700');

        new DeclarationNote(str_repeat('a', 701));
    }
}

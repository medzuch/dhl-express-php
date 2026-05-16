<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\BankDetails;
use PHPUnit\Framework\TestCase;

final class BankDetailsTest extends TestCase
{
    public function testToArrayEmitsOnlyPopulatedFields(): void
    {
        $dto = new BankDetails(name: 'Russian Bank');

        self::assertSame(['name' => 'Russian Bank'], $dto->toArray());
    }

    public function testToArrayWithAllFields(): void
    {
        $dto = new BankDetails(
            name: 'Russian Bank',
            settlementLocalCurrency: 'RUB',
            settlementForeignCurrency: 'USD',
        );

        self::assertSame(
            [
                'name' => 'Russian Bank',
                'settlementLocalCurrency' => 'RUB',
                'settlementForeignCurrency' => 'USD',
            ],
            $dto->toArray(),
        );
    }

    public function testToArrayCanBeEmptyButCallerMustSetAtLeastOneFieldOnTheWire(): void
    {
        // The DTO itself doesn't enforce minProperties:1 — that's the wire
        // contract; the DTO would let an empty instance through. Document
        // the lenient behavior so callers know.
        self::assertSame([], (new BankDetails())->toArray());
    }
}

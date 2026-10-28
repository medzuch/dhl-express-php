<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\ParentShipment;
use PHPUnit\Framework\TestCase;

final class ParentShipmentTest extends TestCase
{
    public function testSerializesExactlyProductCodeAndPackagesCount(): void
    {
        $parent = new ParentShipment(productCode: 'P', packagesCount: 3);

        self::assertSame(['productCode' => 'P', 'packagesCount' => 3], $parent->toArray());
    }

    public function testOmitsUnsetFields(): void
    {
        self::assertSame([], (new ParentShipment())->toArray());
    }
}

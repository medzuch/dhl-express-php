<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\DangerousGoods;
use Medzuch\DhlExpress\Dto\Shipment\ValueAddedService;
use Medzuch\DhlExpress\Enum\DangerousGoodsContentId;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use PHPUnit\Framework\TestCase;

final class ValueAddedServiceTest extends TestCase
{
    public function testToArrayWithServiceCodeOnly(): void
    {
        $vas = new ValueAddedService(serviceCode: 'WY');

        self::assertSame(['serviceCode' => 'WY'], $vas->toArray());
    }

    public function testToArrayWithAllScalarFields(): void
    {
        $vas = new ValueAddedService(
            serviceCode: 'II',
            value: 250.0,
            currency: new CurrencyCode('EUR'),
            method: 'cash',
        );

        self::assertSame(
            [
                'serviceCode' => 'II',
                'value' => 250.0,
                'currency' => 'EUR',
                'method' => 'cash',
            ],
            $vas->toArray(),
        );
    }

    public function testToArrayNestsDangerousGoodsAsSingleItemArray(): void
    {
        $vas = new ValueAddedService(
            serviceCode: 'HY',
            dangerousGoods: new DangerousGoods(contentId: DangerousGoodsContentId::DryIceUN1845),
        );

        $payload = $vas->toArray();

        self::assertArrayHasKey('dangerousGoods', $payload);
        self::assertIsArray($payload['dangerousGoods']);
        self::assertCount(1, $payload['dangerousGoods']);
        self::assertSame('901', $payload['dangerousGoods'][0]['contentId']);
    }

    public function testWithDangerousGoodsReturnsNewInstance(): void
    {
        $original = new ValueAddedService(serviceCode: 'HY');
        $dg = new DangerousGoods(contentId: DangerousGoodsContentId::BiologicalSubstanceUN3373);

        $cloned = $original->withDangerousGoods($dg);

        // Immutability — original unchanged, returned instance carries the DG block.
        self::assertNotSame($original, $cloned);
        self::assertNull($original->dangerousGoods);
        self::assertSame($dg, $cloned->dangerousGoods);
        self::assertSame('HY', $cloned->serviceCode);
    }
}

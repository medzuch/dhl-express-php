<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\DangerousGoods;
use Medzuch\DhlExpress\Enum\DangerousGoodsContentId;
use PHPUnit\Framework\TestCase;

final class DangerousGoodsTest extends TestCase
{
    public function testToArrayWithRequiredFieldOnly(): void
    {
        $dto = new DangerousGoods(contentId: DangerousGoodsContentId::DryIceUN1845);

        $result = $dto->toArray();

        self::assertSame(['contentId' => '901'], $result);
    }

    public function testToArrayWithAllFields(): void
    {
        $dto = new DangerousGoods(
            contentId: DangerousGoodsContentId::DryIceUN1845,
            dryIceTotalNetWeight: 2.5,
            customDescription: 'Dry ice for preservation',
            unCodes: ['1845'],
        );

        $result = $dto->toArray();

        self::assertSame('901', $result['contentId']);
        self::assertSame(2.5, $result['dryIceTotalNetWeight']);
        self::assertSame('Dry ice for preservation', $result['customDescription']);
        self::assertSame(['1845'], $result['unCodes']);
    }

    public function testToArrayOmitsNullOptionals(): void
    {
        $dto = new DangerousGoods(contentId: DangerousGoodsContentId::BiologicalSubstanceUN3373);

        $result = $dto->toArray();

        self::assertArrayNotHasKey('dryIceTotalNetWeight', $result);
        self::assertArrayNotHasKey('customDescription', $result);
        self::assertArrayNotHasKey('unCodes', $result);
    }

    public function testContentIdSerializesAsWireValue(): void
    {
        $dto = new DangerousGoods(contentId: DangerousGoodsContentId::A01);

        self::assertSame('A01', $dto->toArray()['contentId']);
    }
}

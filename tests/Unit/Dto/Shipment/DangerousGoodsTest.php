<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use InvalidArgumentException;
use Medzuch\DhlExpress\Dto\Shipment\DangerousGoods;
use Medzuch\DhlExpress\Enum\DangerousGoodsContentId;
use Medzuch\DhlExpress\Enum\UnitSystem;
use PHPUnit\Framework\Attributes\DataProvider;
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
        self::assertArrayNotHasKey('packagesCount', $result);
        self::assertArrayNotHasKey('dryIceUnitOfMeasurement', $result);
    }

    public function testApi340FieldsAreSerialized(): void
    {
        $dto = new DangerousGoods(
            contentId: DangerousGoodsContentId::DryIceUN1845,
            dryIceTotalNetWeight: 2.5,
            packagesCount: 3,
            dryIceUnitOfMeasurement: UnitSystem::Metric,
        );

        $result = $dto->toArray();

        self::assertSame(3, $result['packagesCount']);
        self::assertSame('metric', $result['dryIceUnitOfMeasurement']);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function validPackagesCounts(): iterable
    {
        yield 'minimum' => [1];
        yield 'maximum' => [999];
    }

    #[DataProvider('validPackagesCounts')]
    public function testPackagesCountWithinRangeIsAccepted(int $count): void
    {
        $dto = new DangerousGoods(contentId: DangerousGoodsContentId::A01, packagesCount: $count);

        self::assertSame($count, $dto->toArray()['packagesCount']);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidPackagesCounts(): iterable
    {
        yield 'zero' => [0];
        yield 'above maximum' => [1000];
    }

    #[DataProvider('invalidPackagesCounts')]
    public function testPackagesCountOutOfRangeIsRejected(int $count): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('packagesCount');

        new DangerousGoods(contentId: DangerousGoodsContentId::A01, packagesCount: $count);
    }

    public function testCustomDescriptionOfOneThousandCharactersIsAccepted(): void
    {
        $description = str_repeat('a', 1000);
        $dto = new DangerousGoods(contentId: DangerousGoodsContentId::A01, customDescription: $description);

        self::assertSame($description, $dto->toArray()['customDescription']);
    }

    public function testCustomDescriptionLongerThanOneThousandCharactersIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('customDescription');

        new DangerousGoods(contentId: DangerousGoodsContentId::A01, customDescription: str_repeat('a', 1001));
    }

    public function testContentIdSerializesAsWireValue(): void
    {
        $dto = new DangerousGoods(contentId: DangerousGoodsContentId::A01);

        self::assertSame('A01', $dto->toArray()['contentId']);
    }
}

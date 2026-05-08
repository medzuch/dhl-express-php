<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Common;

use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class RatePackageTest extends TestCase
{
    public function testWeightOnlyPackageEmitsBareWeight(): void
    {
        $package = new RatePackage(
            weight: new Weight(1.5, WeightUnit::KG),
        );

        self::assertSame(['weight' => 1.5], $package->toArray());
    }

    public function testFullPackageEmitsTypeCodeAndDimensions(): void
    {
        $package = new RatePackage(
            weight: new Weight(10.0, WeightUnit::KG),
            dimensions: new Dimensions(25.0, 35.0, 15.0, DimensionUnit::CM),
            typeCode: PackageTypeCode::Box3,
        );

        self::assertSame(
            [
                'weight' => 10.0,
                'typeCode' => '3BX',
                'dimensions' => [
                    'length' => 25.0,
                    'width' => 35.0,
                    'height' => 15.0,
                ],
            ],
            $package->toArray(),
        );
    }
}

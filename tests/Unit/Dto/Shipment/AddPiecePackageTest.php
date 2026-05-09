<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\AddPiecePackage;
use Medzuch\DhlExpress\Dto\Shipment\PackageReference;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\PackageReferenceTypeCode;
use Medzuch\DhlExpress\Enum\PackageTypeCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use PHPUnit\Framework\TestCase;

final class AddPiecePackageTest extends TestCase
{
    public function testToArrayWithOnlyWeightRequired(): void
    {
        $package = new AddPiecePackage(weight: 1.5);
        $array = $package->toArray();

        self::assertSame(1.5, $array['weight']);
        self::assertArrayNotHasKey('typeCode', $array);
        self::assertArrayNotHasKey('dimensions', $array);
        self::assertArrayNotHasKey('customerReferences', $array);
        self::assertArrayNotHasKey('description', $array);
    }

    public function testToArrayWithAllOptionalFields(): void
    {
        $dimensions = new Dimensions(10.0, 20.0, 15.0, DimensionUnit::CM);
        $reference = new PackageReference('REF-001', PackageReferenceTypeCode::CU);

        $package = new AddPiecePackage(
            weight: 2.5,
            typeCode: PackageTypeCode::XPD,
            dimensions: $dimensions,
            customerReferences: [$reference],
            description: 'Test package',
        );

        $array = $package->toArray();

        self::assertSame(2.5, $array['weight']);
        self::assertSame('XPD', $array['typeCode']);
        self::assertSame(['length' => 10.0, 'width' => 20.0, 'height' => 15.0], $array['dimensions']);
        self::assertCount(1, $array['customerReferences']);
        self::assertSame('REF-001', $array['customerReferences'][0]['value']);
        self::assertSame('Test package', $array['description']);
    }

    public function testCustomerReferencesOmittedWhenEmpty(): void
    {
        $package = new AddPiecePackage(weight: 0.5, customerReferences: []);
        $array = $package->toArray();

        self::assertArrayNotHasKey('customerReferences', $array);
    }
}

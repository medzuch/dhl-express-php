<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\Identifier;
use Medzuch\DhlExpress\Dto\Shipment\LabelBarcode;
use Medzuch\DhlExpress\Dto\Shipment\LabelText;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Enum\BarcodeSymbology;
use Medzuch\DhlExpress\Enum\IdentifierTypeCode;
use Medzuch\DhlExpress\Enum\LabelBarcodePosition;
use Medzuch\DhlExpress\Enum\LabelTextPosition;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class PackageTest extends TestCase
{
    public function testIdentifiersSerialize(): void
    {
        $package = new Package(
            weight: new Weight(1.0, WeightUnit::KG),
            identifiers: [
                new Identifier(typeCode: IdentifierTypeCode::ShipmentId, value: 'WB123'),
                new Identifier(typeCode: IdentifierTypeCode::PieceId, value: 'P1', dataIdentifier: '00'),
            ],
        );

        $array = $package->toArray();

        self::assertCount(2, $array['identifiers']);
        self::assertSame('shipmentId', $array['identifiers'][0]['typeCode']);
        self::assertSame('WB123', $array['identifiers'][0]['value']);
        self::assertArrayNotHasKey('dataIdentifier', $array['identifiers'][0]);
        self::assertSame('pieceId', $array['identifiers'][1]['typeCode']);
        self::assertSame('00', $array['identifiers'][1]['dataIdentifier']);
    }

    public function testLabelBarcodesSerialize(): void
    {
        $package = new Package(
            weight: new Weight(1.0, WeightUnit::KG),
            labelBarcodes: [
                new LabelBarcode(
                    position: LabelBarcodePosition::Left,
                    symbologyCode: BarcodeSymbology::Code93,
                    content: 'X1',
                    textBelowBarcode: 'left text',
                ),
                new LabelBarcode(
                    position: LabelBarcodePosition::Right,
                    symbologyCode: BarcodeSymbology::Code39,
                    content: 'X2',
                    textBelowBarcode: 'right text',
                ),
            ],
        );

        $array = $package->toArray();

        self::assertCount(2, $array['labelBarcodes']);
        self::assertSame('left', $array['labelBarcodes'][0]['position']);
        self::assertSame('93', $array['labelBarcodes'][0]['symbologyCode']);
        self::assertSame('right', $array['labelBarcodes'][1]['position']);
        self::assertSame('39', $array['labelBarcodes'][1]['symbologyCode']);
    }

    public function testLabelTextSerialize(): void
    {
        $package = new Package(
            weight: new Weight(1.0, WeightUnit::KG),
            labelText: [
                new LabelText(position: LabelTextPosition::Left1, caption: 'Order', value: '#42'),
                new LabelText(position: LabelTextPosition::Right3, caption: 'Note', value: 'fragile'),
            ],
        );

        $array = $package->toArray();

        self::assertCount(2, $array['labelText']);
        self::assertSame('left1', $array['labelText'][0]['position']);
        self::assertSame('right3', $array['labelText'][1]['position']);
    }

    public function testNewArraysOmittedWhenEmpty(): void
    {
        $package = new Package(weight: new Weight(1.0, WeightUnit::KG));

        $array = $package->toArray();

        self::assertArrayNotHasKey('identifiers', $array);
        self::assertArrayNotHasKey('labelBarcodes', $array);
        self::assertArrayNotHasKey('labelText', $array);
    }
}

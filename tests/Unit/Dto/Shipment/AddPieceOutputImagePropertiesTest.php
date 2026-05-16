<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\AddPieceOutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\CustomerBarcode;
use Medzuch\DhlExpress\Dto\Shipment\CustomerLogo;
use Medzuch\DhlExpress\Enum\BarcodeSymbology;
use Medzuch\DhlExpress\Enum\CustomerLogoFileFormat;
use PHPUnit\Framework\TestCase;

final class AddPieceOutputImagePropertiesTest extends TestCase
{
    public function testCustomerBarcodesAndLogosSerialize(): void
    {
        $dto = new AddPieceOutputImageProperties(
            customerBarcodes: [
                new CustomerBarcode(content: 'X', symbologyCode: BarcodeSymbology::Code128),
            ],
            customerLogos: [
                new CustomerLogo(fileFormat: CustomerLogoFileFormat::PNG, content: 'b64'),
            ],
        );

        $payload = $dto->toArray();

        self::assertCount(1, $payload['customerBarcodes']);
        self::assertSame('X', $payload['customerBarcodes'][0]['content']);
        self::assertCount(1, $payload['customerLogos']);
        self::assertSame('PNG', $payload['customerLogos'][0]['fileFormat']);
    }

    public function testCustomerBarcodesAndLogosOmittedWhenEmpty(): void
    {
        $dto = new AddPieceOutputImageProperties();
        $payload = $dto->toArray();

        self::assertArrayNotHasKey('customerBarcodes', $payload);
        self::assertArrayNotHasKey('customerLogos', $payload);
    }
}

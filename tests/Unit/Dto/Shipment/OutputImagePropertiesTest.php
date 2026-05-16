<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\CustomerBarcode;
use Medzuch\DhlExpress\Dto\Shipment\CustomerLogo;
use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
use Medzuch\DhlExpress\Dto\Shipment\OutputImageProperties;
use Medzuch\DhlExpress\Enum\BarcodeSymbology;
use Medzuch\DhlExpress\Enum\CustomerLogoFileFormat;
use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use PHPUnit\Framework\TestCase;

final class OutputImagePropertiesTest extends TestCase
{
    public function testToArrayEmpty(): void
    {
        self::assertSame([], (new OutputImageProperties())->toArray());
    }

    public function testToArrayWithEncodingAndPrinterDpi(): void
    {
        $dto = new OutputImageProperties(
            encodingFormat: LabelEncodingFormat::Pdf,
            printerDPI: 300,
        );

        self::assertSame(
            ['encodingFormat' => 'pdf', 'printerDPI' => 300],
            $dto->toArray(),
        );
    }

    public function testRenderDHLLogoAndFitLabelsToA4LiveOnImageOptionNotHere(): void
    {
        // Smoke check — the DTO no longer accepts these as constructor args.
        $reflection = new \ReflectionClass(OutputImageProperties::class);
        $params = $reflection->getConstructor()?->getParameters() ?? [];
        $names = array_map(static fn (\ReflectionParameter $p): string => $p->getName(), $params);

        self::assertNotContains('renderDHLLogo', $names);
        self::assertNotContains('fitLabelsToA4', $names);
    }

    public function testImageOptionsArrayAppearsInPayload(): void
    {
        $dto = new OutputImageProperties(
            imageOptions: [new ImageOption(typeCode: 'label', isRequested: true)],
        );

        $payload = $dto->toArray();

        self::assertArrayHasKey('imageOptions', $payload);
        self::assertCount(1, $payload['imageOptions']);
        self::assertSame('label', $payload['imageOptions'][0]['typeCode']);
    }

    public function testCustomerBarcodesAndLogosSerialize(): void
    {
        $dto = new OutputImageProperties(
            customerBarcodes: [
                new CustomerBarcode(
                    content: 'CUST-001',
                    symbologyCode: BarcodeSymbology::Code128,
                    textBelowBarcode: 'Order #1',
                ),
            ],
            customerLogos: [
                new CustomerLogo(
                    fileFormat: CustomerLogoFileFormat::PNG,
                    content: 'base64data',
                ),
            ],
        );

        $payload = $dto->toArray();

        self::assertCount(1, $payload['customerBarcodes']);
        self::assertSame('CUST-001', $payload['customerBarcodes'][0]['content']);
        self::assertSame('128', $payload['customerBarcodes'][0]['symbologyCode']);
        self::assertSame('Order #1', $payload['customerBarcodes'][0]['textBelowBarcode']);

        self::assertCount(1, $payload['customerLogos']);
        self::assertSame('PNG', $payload['customerLogos'][0]['fileFormat']);
        self::assertSame('base64data', $payload['customerLogos'][0]['content']);
    }

    public function testSplitAndCombineTogglesSerialize(): void
    {
        $dto = new OutputImageProperties(
            splitTransportAndWaybillDocLabels: true,
            allDocumentsInOneImage: false,
            splitDocumentsByPages: true,
            splitInvoiceAndReceipt: false,
            receiptAndLabelsInOneImage: true,
        );

        $payload = $dto->toArray();

        self::assertTrue($payload['splitTransportAndWaybillDocLabels']);
        self::assertFalse($payload['allDocumentsInOneImage']);
        self::assertTrue($payload['splitDocumentsByPages']);
        self::assertFalse($payload['splitInvoiceAndReceipt']);
        self::assertTrue($payload['receiptAndLabelsInOneImage']);
    }
}

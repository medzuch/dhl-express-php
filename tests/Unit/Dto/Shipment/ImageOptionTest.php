<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
use Medzuch\DhlExpress\Enum\InvoiceImageType;
use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use PHPUnit\Framework\TestCase;

final class ImageOptionTest extends TestCase
{
    public function testToArrayWithTypeCodeOnly(): void
    {
        self::assertSame(
            ['typeCode' => 'label'],
            (new ImageOption(typeCode: 'label'))->toArray(),
        );
    }

    public function testToArrayWithAllFields(): void
    {
        $dto = new ImageOption(
            typeCode: 'label',
            isRequested: true,
            templateName: 'ECOM26_84_001',
            encodingFormat: LabelEncodingFormat::Pdf,
            hideAccountNumber: true,
            numberOfCopies: 2,
            renderDHLLogo: true,
            fitLabelsToA4: false,
        );

        self::assertSame(
            [
                'typeCode' => 'label',
                'isRequested' => true,
                'templateName' => 'ECOM26_84_001',
                'encodingFormat' => 'pdf',
                'hideAccountNumber' => true,
                'numberOfCopies' => 2,
                'renderDHLLogo' => true,
                'fitLabelsToA4' => false,
            ],
            $dto->toArray(),
        );
    }

    public function testInvoiceAndLanguageFieldsSerialize(): void
    {
        $dto = new ImageOption(
            typeCode: 'invoice',
            invoiceType: InvoiceImageType::Proforma,
            languageCode: 'eng',
            languageCountryCode: 'us',
            languageScriptCode: 'Latn',
        );

        $payload = $dto->toArray();

        self::assertSame('proforma', $payload['invoiceType']);
        self::assertSame('eng', $payload['languageCode']);
        self::assertSame('us', $payload['languageCountryCode']);
        self::assertSame('Latn', $payload['languageScriptCode']);
    }

    public function testLabelAndReceiptTextsSerialize(): void
    {
        $dto = new ImageOption(
            typeCode: 'label',
            labelFreeText: 'Free text on label',
            labelCustomerDataText: 'Customer data',
            shipmentReceiptCustomerDataText: 'Declaration text',
        );

        $payload = $dto->toArray();

        self::assertSame('Free text on label', $payload['labelFreeText']);
        self::assertSame('Customer data', $payload['labelCustomerDataText']);
        self::assertSame('Declaration text', $payload['shipmentReceiptCustomerDataText']);
    }

    public function testRenderDHLLogoSerializesOnlyWhenSet(): void
    {
        $payload = (new ImageOption(typeCode: 'label', renderDHLLogo: true))->toArray();

        self::assertArrayHasKey('renderDHLLogo', $payload);
        self::assertTrue($payload['renderDHLLogo']);
        self::assertArrayNotHasKey('fitLabelsToA4', $payload);
    }
}

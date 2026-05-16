<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
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

    public function testRenderDHLLogoSerializesOnlyWhenSet(): void
    {
        $payload = (new ImageOption(typeCode: 'label', renderDHLLogo: true))->toArray();

        self::assertArrayHasKey('renderDHLLogo', $payload);
        self::assertTrue($payload['renderDHLLogo']);
        self::assertArrayNotHasKey('fitLabelsToA4', $payload);
    }
}

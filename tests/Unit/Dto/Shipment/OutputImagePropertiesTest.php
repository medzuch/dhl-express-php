<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
use Medzuch\DhlExpress\Dto\Shipment\OutputImageProperties;
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
}

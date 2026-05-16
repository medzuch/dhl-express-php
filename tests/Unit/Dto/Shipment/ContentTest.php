<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\Content;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class ContentTest extends TestCase
{
    public function testToArrayWithRequiredFieldsOnly(): void
    {
        $content = $this->makeMinimalContent();

        $payload = $content->toArray();

        self::assertSame('DAP', $payload['incoterm']);
        self::assertSame('metric', $payload['unitOfMeasurement']);
        self::assertArrayNotHasKey('areMorePackagesToBeAddedLater', $payload);
        self::assertArrayNotHasKey('USFilingTypeValue', $payload);
    }

    public function testAreMorePackagesToBeAddedLaterAppearsWhenSet(): void
    {
        $content = $this->makeMinimalContent(areMorePackagesToBeAddedLater: true);

        $payload = $content->toArray();

        self::assertArrayHasKey('areMorePackagesToBeAddedLater', $payload);
        self::assertTrue($payload['areMorePackagesToBeAddedLater']);
    }

    public function testUSFilingTypeValueSerializesWithCanonicalKey(): void
    {
        $content = $this->makeMinimalContent(usFilingTypeValue: 'X20240515123456');

        $payload = $content->toArray();

        self::assertArrayHasKey('USFilingTypeValue', $payload);
        self::assertSame('X20240515123456', $payload['USFilingTypeValue']);
    }

    private function makeMinimalContent(
        ?bool $areMorePackagesToBeAddedLater = null,
        ?string $usFilingTypeValue = null,
    ): Content {
        return new Content(
            packages: [new Package(weight: new Weight(1.0, WeightUnit::KG))],
            isCustomsDeclarable: false,
            description: 'Books',
            incoterm: Incoterm::DAP,
            unitOfMeasurement: UnitSystem::Metric,
            areMorePackagesToBeAddedLater: $areMorePackagesToBeAddedLater,
            USFilingTypeValue: $usFilingTypeValue,
        );
    }
}

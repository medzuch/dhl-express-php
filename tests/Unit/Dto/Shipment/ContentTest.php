<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Shipment\Content;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportInvoice;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\InvoiceFunction;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
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
        self::assertArrayNotHasKey('exportDeclaration', $payload);
        self::assertArrayNotHasKey('exportDeclarations', $payload);
        self::assertArrayNotHasKey('dimensionUnitOfMeasurement', $payload);
    }

    public function testExportDeclarationsSerializeAsListInOrder(): void
    {
        $content = new Content(
            packages: [new Package(weight: new Weight(1.0, WeightUnit::KG))],
            isCustomsDeclarable: true,
            description: 'Books',
            incoterm: Incoterm::DAP,
            unitOfMeasurement: UnitSystem::Metric,
            exportDeclarations: [$this->makeDeclaration('INV-1'), $this->makeDeclaration('INV-2')],
        );

        $payload = $content->toArray();

        self::assertArrayNotHasKey('exportDeclaration', $payload);
        self::assertCount(2, $payload['exportDeclarations']);
        self::assertSame('INV-1', $payload['exportDeclarations'][0]['invoice']['number']);
        self::assertSame('INV-2', $payload['exportDeclarations'][1]['invoice']['number']);
    }

    public function testIncotermIsOmittedWhenNull(): void
    {
        $content = new Content(
            packages: [new Package(weight: new Weight(1.0, WeightUnit::KG))],
            isCustomsDeclarable: false,
            description: 'Books',
            incoterm: null,
            unitOfMeasurement: UnitSystem::Metric,
        );

        self::assertArrayNotHasKey('incoterm', $content->toArray());
    }

    public function testDimensionUnitOfMeasurementIsEmittedWhenSet(): void
    {
        $content = new Content(
            packages: [new Package(weight: new Weight(1.0, WeightUnit::KG))],
            isCustomsDeclarable: false,
            description: 'Books',
            incoterm: Incoterm::DAP,
            unitOfMeasurement: UnitSystem::Metric,
            dimensionUnitOfMeasurement: UnitSystem::Imperial,
        );

        $payload = $content->toArray();

        self::assertSame('metric', $payload['unitOfMeasurement']);
        self::assertSame('imperial', $payload['dimensionUnitOfMeasurement']);
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

    private function makeDeclaration(string $invoiceNumber): ExportDeclaration
    {
        return new ExportDeclaration(
            lineItems: [
                new ExportLineItem(
                    number: 1,
                    description: 'Book',
                    price: 10.00,
                    quantity: new LineItemQuantity(1, LineItemQuantityUnit::PCS),
                    manufacturerCountry: 'CZ',
                    weight: new LineItemWeight(netValue: 1.0),
                ),
            ],
            invoice: new ExportInvoice(
                number: $invoiceNumber,
                date: new DateTimeImmutable('2026-05-01'),
                function: InvoiceFunction::Export,
            ),
            incoterm: Incoterm::DAP,
        );
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

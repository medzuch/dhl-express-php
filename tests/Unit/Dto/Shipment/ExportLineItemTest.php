<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\CommodityCode;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Enum\CommodityCodeType;
use Medzuch\DhlExpress\Enum\ExportReasonType;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use PHPUnit\Framework\TestCase;

final class ExportLineItemTest extends TestCase
{
    public function testToArrayWithRequiredFieldsOnly(): void
    {
        $item = new ExportLineItem(
            number: 1,
            description: 'Test product',
            price: 100.00,
            quantity: new LineItemQuantity(5, LineItemQuantityUnit::PCS),
            manufacturerCountry: 'CZ',
            weight: new LineItemWeight(netValue: 1.0, grossValue: 1.2),
        );

        $result = $item->toArray();

        self::assertSame(1, $result['number']);
        self::assertSame('Test product', $result['description']);
        self::assertSame(100.00, $result['price']);
        self::assertSame(['value' => 5, 'unitOfMeasurement' => 'PCS'], $result['quantity']);
        self::assertSame('CZ', $result['manufacturerCountry']);
        self::assertSame(['netValue' => 1.0, 'grossValue' => 1.2], $result['weight']);
    }

    public function testToArrayWithOptionalFields(): void
    {
        $item = new ExportLineItem(
            number: 1,
            description: 'Test product',
            price: 100.00,
            quantity: new LineItemQuantity(5, LineItemQuantityUnit::PCS),
            manufacturerCountry: 'CZ',
            weight: new LineItemWeight(netValue: 1.0, grossValue: 1.2),
            exportReasonType: ExportReasonType::Commercial,
            commodityCodes: [new CommodityCode(CommodityCodeType::Outbound, '8471.30')],
            isTaxesPaid: false,
            preCalculatedLineItemTotalValue: 500.00,
        );

        $result = $item->toArray();

        self::assertSame('commercial_purpose_or_sale', $result['exportReasonType']);
        self::assertCount(1, $result['commodityCodes']);
        self::assertSame('outbound', $result['commodityCodes'][0]['typeCode']);
        self::assertSame('8471.30', $result['commodityCodes'][0]['value']);
        self::assertFalse($result['isTaxesPaid']);
        self::assertSame(500.00, $result['preCalculatedLineItemTotalValue']);
    }

    public function testToArrayOmitsNullOptionals(): void
    {
        $item = new ExportLineItem(
            number: 1,
            description: 'Test product',
            price: 100.00,
            quantity: new LineItemQuantity(5, LineItemQuantityUnit::PCS),
            manufacturerCountry: 'CZ',
            weight: new LineItemWeight(netValue: 1.0, grossValue: 1.2),
        );

        $result = $item->toArray();

        self::assertArrayNotHasKey('exportReasonType', $result);
        self::assertArrayNotHasKey('commodityCodes', $result);
        self::assertArrayNotHasKey('isTaxesPaid', $result);
        self::assertArrayNotHasKey('customerReferences', $result);
        self::assertArrayNotHasKey('customsDocuments', $result);
        self::assertArrayNotHasKey('preCalculatedLineItemTotalValue', $result);
        self::assertArrayNotHasKey('additionalInformation', $result);
    }

    public function testAdditionalInformationIsSerialized(): void
    {
        $item = $this->itemWithAdditionalInformation(['Made of cotton']);

        self::assertSame(['Made of cotton'], $item->toArray()['additionalInformation']);
    }

    public function testAdditionalInformationAcceptsEightEntries(): void
    {
        $item = $this->itemWithAdditionalInformation(array_fill(0, 8, 'info'));

        self::assertCount(8, $item->additionalInformation);
    }

    public function testAdditionalInformationRejectsMoreThanEightEntries(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('additionalInformation');

        $this->itemWithAdditionalInformation(array_fill(0, 9, 'info'));
    }

    /**
     * @param list<string> $additionalInformation
     */
    private function itemWithAdditionalInformation(array $additionalInformation): ExportLineItem
    {
        return new ExportLineItem(
            number: 1,
            description: 'Test product',
            price: 100.00,
            quantity: new LineItemQuantity(5, LineItemQuantityUnit::PCS),
            manufacturerCountry: 'CZ',
            weight: new LineItemWeight(netValue: 1.0),
            additionalInformation: $additionalInformation,
        );
    }
}

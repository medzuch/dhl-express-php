<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Builder;

use DateTimeImmutable;
use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\DangerousGoods;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Dto\Shipment\ValueAddedService;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DangerousGoodsContentId;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\TestCase;

final class CreateShipmentBuilderPhase4bTest extends TestCase
{
    // ----- Phase 4b: exportDeclaration replaces customs guardrail -----

    public function testCustomsDeclarableWithExportDeclarationSucceeds(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration($this->makeExportDeclaration())
            ->build();

        self::assertTrue($request->content->isCustomsDeclarable);
        self::assertNotNull($request->content->exportDeclaration);
    }

    public function testCustomsDeclarableWithoutExportDeclarationFails(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(true);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('content.exportDeclaration', $fields);
        }
    }

    public function testNonCustomsDeclarableDoesNotRequireExportDeclaration(): void
    {
        // Should succeed without exportDeclaration
        $request = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(false)
            ->build();

        self::assertFalse($request->content->isCustomsDeclarable);
        self::assertNull($request->content->exportDeclaration);
    }

    // ----- Phase 4b: DG VAS rule -----

    public function testDangerousGoodsVasWithoutDgBlockFails(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withValueAddedService(new ValueAddedService('HY'));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('dangerousGoods', $fields);
        }
    }

    public function testDangerousGoodsVasWithDgBlockSucceeds(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withValueAddedService(new ValueAddedService('HY'))
            ->withDangerousGoods(new DangerousGoods(
                contentId: DangerousGoodsContentId::BiologicalSubstanceUN3373,
            ))
            ->build();

        // DG payload nests INSIDE the matching VAS; no root-level dangerousGoods.
        self::assertCount(1, $request->valueAddedServices);
        self::assertNotNull($request->valueAddedServices[0]->dangerousGoods);
        self::assertSame(
            DangerousGoodsContentId::BiologicalSubstanceUN3373,
            $request->valueAddedServices[0]->dangerousGoods->contentId,
        );
    }

    public function testNonDgVasDoesNotTriggerDgRule(): void
    {
        // VAS code 'II' (insurance) is not a DG service code — should not fail
        $request = $this->minimalDomesticBuilder()
            ->withValueAddedService(new ValueAddedService('II'))
            ->withDeclaredValue(100.0, 'EUR')
            ->build();

        self::assertCount(1, $request->valueAddedServices);
        self::assertNull($request->valueAddedServices[0]->dangerousGoods);
    }

    public function testDangerousGoodsBlockWithoutDgVasFails(): void
    {
        // Inverse of the DG VAS rule: a DG block must have a DG-coded VAS to ride on.
        $builder = $this->minimalDomesticBuilder()
            ->withDangerousGoods(new DangerousGoods(
                contentId: DangerousGoodsContentId::DryIceUN1845,
            ));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('valueAddedServices', $fields);
        }
    }

    // ----- Phase 4b: Insurance VAS rule -----

    public function testInsuranceVasWithoutDeclaredValueFails(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withValueAddedService(new ValueAddedService('II'));

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('content.declaredValue', $fields);
        }
    }

    public function testInsuranceVasWithDeclaredValueSucceeds(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withValueAddedService(new ValueAddedService('II'))
            ->withDeclaredValue(150.0, 'CZK')
            ->build();

        self::assertSame(150.0, $request->content->declaredValue);
        self::assertSame('CZK', $request->content->declaredValueCurrency);
    }

    // ----- Phase 4b: DDP incoterm rule -----

    public function testDdpIncotermWithoutDutiesTaxesAccountFails(): void
    {
        $builder = $this->minimalDomesticBuilder()
            ->withIncoterm(Incoterm::DDP)
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration($this->makeExportDeclaration());

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('accounts', $fields);
            $messages = array_column($exception->errors(), 'message');
            $found = array_filter($messages, static fn (string $m): bool => str_contains($m, 'duties-taxes'));
            self::assertNotEmpty($found, 'DDP error message should mention duties-taxes');
        }
    }

    public function testDdpIncotermWithDutiesTaxesAccountSucceeds(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withIncoterm(Incoterm::DDP)
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration($this->makeExportDeclaration())
            ->withAccount(new Account(AccountTypeCode::DutiesTaxes, new AccountNumber('999999999')))
            ->build();

        self::assertSame(Incoterm::DDP, $request->content->incoterm);
    }

    public function testNonDdpIncotermDoesNotRequireDutiesTaxesAccount(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withIncoterm(Incoterm::DAP)
            ->build();

        self::assertSame(Incoterm::DAP, $request->content->incoterm);
    }

    // ----- Phase 4b: withDeclaredValue -----

    public function testWithDeclaredValueSetsContentFields(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withDeclaredValue(500.0, 'EUR')
            ->build();

        self::assertSame(500.0, $request->content->declaredValue);
        self::assertSame('EUR', $request->content->declaredValueCurrency);
    }

    // ----- DangerousGoods nests inside valueAddedServices on the wire -----

    public function testDangerousGoodsAppearsNestedInsideValueAddedServices(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withValueAddedService(new ValueAddedService('HY'))
            ->withDangerousGoods(new DangerousGoods(contentId: DangerousGoodsContentId::DryIceUN1845))
            ->build();

        $payload = $request->toArray();

        // Root-level dangerousGoods must NOT exist — the spec marks the
        // request schema as additionalProperties: false.
        self::assertArrayNotHasKey('dangerousGoods', $payload);

        self::assertArrayHasKey('valueAddedServices', $payload);
        self::assertIsArray($payload['valueAddedServices']);
        self::assertCount(1, $payload['valueAddedServices']);

        $vas = $payload['valueAddedServices'][0];
        self::assertSame('HY', $vas['serviceCode']);
        self::assertArrayHasKey('dangerousGoods', $vas);
        self::assertIsArray($vas['dangerousGoods']);
        self::assertCount(1, $vas['dangerousGoods']);
        self::assertSame('901', $vas['dangerousGoods'][0]['contentId']);
    }

    // ----- Phase 4b: exportDeclaration in content toArray -----

    public function testExportDeclarationAppearsInContentToArray(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration($this->makeExportDeclaration())
            ->build();

        $payload = $request->toArray();
        $content = $payload['content'];

        self::assertArrayHasKey('exportDeclaration', $content);
        self::assertArrayHasKey('lineItems', $content['exportDeclaration']);
    }

    // ----- Phase 4b (missing): EU intra-zone exemption -----

    public function testIntraEuCrossBorderDoesNotRequireCustoms(): void
    {
        // CZ → DE: both EU members — isCustomsDeclarable=false must be accepted
        $request = $this->minimalCrossBorderBuilder('CZ', 'DE')
            ->withIsCustomsDeclarable(false)
            ->build();

        self::assertFalse($request->content->isCustomsDeclarable);
    }

    public function testCrossBorderOutsideEuRequiresCustomsDeclarable(): void
    {
        // CZ (EU) → CH (non-EU): isCustomsDeclarable=false must fail
        $builder = $this->minimalCrossBorderBuilder('CZ', 'CH')
            ->withIsCustomsDeclarable(false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('isCustomsDeclarable', $fields);
        }
    }

    public function testCrossBorderBothNonEuRequiresCustomsDeclarable(): void
    {
        // US → CA: neither is EU — isCustomsDeclarable=false must fail
        $builder = $this->minimalCrossBorderBuilder('US', 'CA')
            ->withIsCustomsDeclarable(false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('isCustomsDeclarable', $fields);
        }
    }

    public function testCrossBorderOutsideEuWithCustomsDeclarableTrueSucceeds(): void
    {
        // CZ → CH with isCustomsDeclarable=true + exportDeclaration → valid
        $request = $this->minimalCrossBorderBuilder('CZ', 'CH')
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration($this->makeExportDeclaration())
            ->withDeclaredValue(100.00, 'EUR')
            ->build();

        self::assertTrue($request->content->isCustomsDeclarable);
    }

    public function testDomesticShipmentDoesNotTriggerEuRule(): void
    {
        // CZ → CZ (same country): no cross-border rule applies
        $request = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(false)
            ->build();

        self::assertFalse($request->content->isCustomsDeclarable);
    }

    public function testMonacoIsInEuCustomsTerritory(): void
    {
        // MC (Monaco) is in the EU customs union — FR → MC should not require customs
        $request = $this->minimalCrossBorderBuilder('FR', 'MC')
            ->withIsCustomsDeclarable(false)
            ->build();

        self::assertFalse($request->content->isCustomsDeclarable);
    }

    public function testFrenchOutermostRegionIsInEuCustomsTerritory(): void
    {
        // GP (Guadeloupe), MQ (Martinique), GF (French Guiana), RE (Réunion),
        // YT (Mayotte) are French outermost regions and part of the EU customs
        // territory — shipping from FR to any of them should not require customs
        foreach (['GP', 'MQ', 'GF', 'RE', 'YT'] as $regionCode) {
            $request = $this->minimalCrossBorderBuilder('FR', $regionCode)
                ->withIsCustomsDeclarable(false)
                ->build();

            self::assertFalse(
                $request->content->isCustomsDeclarable,
                "Expected FR → {$regionCode} to be exempt from customs declaration",
            );
        }
    }

    public function testShipmentFromFrenchRegionToEuMemberIsExempt(): void
    {
        // RE → DE: both in EU customs territory — no customs required
        $request = $this->minimalCrossBorderBuilder('RE', 'DE')
            ->withIsCustomsDeclarable(false)
            ->build();

        self::assertFalse($request->content->isCustomsDeclarable);
    }

    public function testShipmentFromFrenchRegionToNonEuRequiresCustoms(): void
    {
        // GP → US: Guadeloupe is in EU customs territory, US is not → customs required
        $builder = $this->minimalCrossBorderBuilder('GP', 'US')
            ->withIsCustomsDeclarable(false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('isCustomsDeclarable', $fields);
        }
    }

    // ----- Northern Ireland BT postcode detection -----

    public function testEuToNorthernIrelandIsExemptFromCustoms(): void
    {
        // DE → GB with BT postcode (NI): Windsor Framework — NI follows EU goods rules
        $request = $this->minimalCrossBorderBuilderWithPostalCodes('DE', '10115', 'GB', 'BT1 1AA')
            ->withIsCustomsDeclarable(false)
            ->build();

        self::assertFalse($request->content->isCustomsDeclarable);
    }

    public function testNorthernIrelandToEuIsExemptFromCustoms(): void
    {
        // GB (BT postcode) → FR: NI is in the EU customs territory
        $request = $this->minimalCrossBorderBuilderWithPostalCodes('GB', 'BT48 6AQ', 'FR', '75001')
            ->withIsCustomsDeclarable(false)
            ->build();

        self::assertFalse($request->content->isCustomsDeclarable);
    }

    public function testGreatBritainToEuRequiresCustoms(): void
    {
        // GB (non-BT postcode, mainland England) → FR: customs required
        $builder = $this->minimalCrossBorderBuilderWithPostalCodes('GB', 'SW1A 1AA', 'FR', '75001')
            ->withIsCustomsDeclarable(false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('isCustomsDeclarable', $fields);
        }
    }

    public function testEuToGreatBritainRequiresCustoms(): void
    {
        // DE → GB (non-BT postcode, mainland): customs required
        $builder = $this->minimalCrossBorderBuilderWithPostalCodes('DE', '10115', 'GB', 'EC1A 1BB')
            ->withIsCustomsDeclarable(false);

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('isCustomsDeclarable', $fields);
        }
    }

    // ----- Phase 4b (missing): line-item sum reconciliation -----

    public function testLineItemSumMatchingDeclaredValueSucceeds(): void
    {
        // 2 items × 50.00 = 100.00 = declaredValue
        $request = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration(new ExportDeclaration(
                lineItems: [
                    new ExportLineItem(
                        number: 1,
                        description: 'Widget A',
                        price: 50.00,
                        quantity: new LineItemQuantity(2, LineItemQuantityUnit::PCS),
                        manufacturerCountry: 'CZ',
                        weight: new LineItemWeight(netValue: 1.0),
                    ),
                ],
            ))
            ->withDeclaredValue(100.00, 'EUR')
            ->build();

        self::assertSame(100.00, $request->content->declaredValue);
    }

    public function testLineItemSumMismatchFailsValidation(): void
    {
        // 1 item × 80.00 ≠ 100.00 → error
        $builder = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration(new ExportDeclaration(
                lineItems: [
                    new ExportLineItem(
                        number: 1,
                        description: 'Widget B',
                        price: 80.00,
                        quantity: new LineItemQuantity(1, LineItemQuantityUnit::PCS),
                        manufacturerCountry: 'CZ',
                        weight: new LineItemWeight(netValue: 1.0),
                    ),
                ],
            ))
            ->withDeclaredValue(100.00, 'EUR');

        try {
            $builder->build();
            self::fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $exception) {
            $fields = array_column($exception->errors(), 'field');
            self::assertContains('content.exportDeclaration.lineItems', $fields);
        }
    }

    public function testLineItemSumWithinToleranceSucceeds(): void
    {
        // floating-point arithmetic: 3 × 33.33 = 99.99, declared 100.00 → diff 0.01 (within tolerance)
        $request = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration(new ExportDeclaration(
                lineItems: [
                    new ExportLineItem(
                        number: 1,
                        description: 'Widget C',
                        price: 33.33,
                        quantity: new LineItemQuantity(3, LineItemQuantityUnit::PCS),
                        manufacturerCountry: 'CZ',
                        weight: new LineItemWeight(netValue: 1.0),
                    ),
                ],
            ))
            ->withDeclaredValue(99.99, 'EUR')
            ->build();

        self::assertSame(99.99, $request->content->declaredValue);
    }

    public function testNoExportDeclarationSkipsReconciliation(): void
    {
        // isCustomsDeclarable=false, no exportDeclaration, but declaredValue set → no reconciliation
        $request = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(false)
            ->withDeclaredValue(100.00, 'EUR')
            ->build();

        self::assertSame(100.00, $request->content->declaredValue);
    }

    public function testNoDeclaredValueSkipsReconciliation(): void
    {
        // exportDeclaration set, but no declaredValue → no reconciliation
        $request = $this->minimalDomesticBuilder()
            ->withIsCustomsDeclarable(true)
            ->withExportDeclaration($this->makeExportDeclaration())
            ->build();

        self::assertNull($request->content->declaredValue);
    }

    // ----- helpers -----

    private function makeExportDeclaration(): ExportDeclaration
    {
        return new ExportDeclaration(
            lineItems: [
                new ExportLineItem(
                    number: 1,
                    description: 'Test product',
                    price: 100.00,
                    quantity: new LineItemQuantity(1, LineItemQuantityUnit::PCS),
                    manufacturerCountry: 'CZ',
                    weight: new LineItemWeight(netValue: 1.0, grossValue: 1.2),
                ),
            ],
        );
    }

    private function minimalCrossBorderBuilder(string $shipperCountry, string $receiverCountry): CreateShipmentBuilder
    {
        return $this->minimalCrossBorderBuilderWithPostalCodes($shipperCountry, '14800', $receiverCountry, '10115');
    }

    private function minimalCrossBorderBuilderWithPostalCodes(
        string $shipperCountry,
        string $shipperPostalCode,
        string $receiverCountry,
        string $receiverPostalCode,
    ): CreateShipmentBuilder {
        return (new CreateShipmentBuilder())
            ->withShipper(new ContactAddress(
                countryCode: new CountryCode($shipperCountry),
                postalCode: new PostalCode($shipperPostalCode),
                cityName: 'Origin City',
                addressLine1: 'Origin Street 1',
                phone: new PhoneNumber('+420 222 333 444'),
                companyName: 'Shipper Co.',
                fullName: 'Shipper Name',
            ))
            ->withReceiver(new ContactAddress(
                countryCode: new CountryCode($receiverCountry),
                postalCode: new PostalCode($receiverPostalCode),
                cityName: 'Destination City',
                addressLine1: 'Destination Street 1',
                phone: new PhoneNumber('+49 30 12345678'),
                companyName: 'Receiver Co.',
                fullName: 'Receiver Name',
            ))
            ->withPlannedShippingDate(new DateTimeImmutable('2026-06-01T13:00:00+00:00'))
            ->withProductCode('P')
            ->withPickupRequested(false)
            ->withContentDescription('Electronics')
            ->withUnitSystem(UnitSystem::Metric)
            ->withIncoterm(Incoterm::DAP)
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withPackage(new Package(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
            ));
    }

    private function minimalDomesticBuilder(): CreateShipmentBuilder
    {
        return (new CreateShipmentBuilder())
            ->withShipper(new ContactAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('14800'),
                cityName: 'Prague',
                addressLine1: 'Vaclavske namesti 1',
                phone: new PhoneNumber('+420 222 333 444'),
                companyName: 'Alfa Trading s.r.o.',
                fullName: 'Jan Nowak',
            ))
            ->withReceiver(new ContactAddress(
                countryCode: new CountryCode('CZ'),
                postalCode: new PostalCode('60200'),
                cityName: 'Brno',
                addressLine1: 'Namesti Svobody 10',
                phone: new PhoneNumber('+420 555 666 777'),
                companyName: 'Receiver s.r.o.',
                fullName: 'Receiver Name',
            ))
            ->withPlannedShippingDate(new DateTimeImmutable('2026-06-01T13:00:00+00:00'))
            ->withProductCode('N')
            ->withPickupRequested(false)
            ->withIsCustomsDeclarable(false)
            ->withContentDescription('Books')
            ->withUnitSystem(UnitSystem::Metric)
            ->withIncoterm(Incoterm::DAP)
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withPackage(new Package(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
            ));
    }
}

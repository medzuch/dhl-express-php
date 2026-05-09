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

        self::assertNotNull($request->dangerousGoods);
    }

    public function testNonDgVasDoesNotTriggerDgRule(): void
    {
        // VAS code 'II' (insurance) is not a DG service code — should not fail
        $request = $this->minimalDomesticBuilder()
            ->withValueAddedService(new ValueAddedService('II'))
            ->withDeclaredValue(100.0, 'EUR')
            ->build();

        self::assertNull($request->dangerousGoods);
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

    // ----- Phase 4b: DangerousGoods in toArray -----

    public function testDangerousGoodsAppearsAsArrayInRequestToArray(): void
    {
        $request = $this->minimalDomesticBuilder()
            ->withValueAddedService(new ValueAddedService('HY'))
            ->withDangerousGoods(new DangerousGoods(contentId: DangerousGoodsContentId::DryIceUN1845))
            ->build();

        $payload = $request->toArray();

        self::assertArrayHasKey('dangerousGoods', $payload);
        self::assertIsArray($payload['dangerousGoods']);
        self::assertCount(1, $payload['dangerousGoods']);
        self::assertSame('901', $payload['dangerousGoods'][0]['contentId']);
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
            ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
            ->withPackage(new Package(
                weight: new Weight(1.0, WeightUnit::KG),
                dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
            ));
    }
}

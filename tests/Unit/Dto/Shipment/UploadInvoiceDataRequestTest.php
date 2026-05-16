<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceCustomerDetails;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceImageOption;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceOutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\InvoiceParty;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Dto\Shipment\RegistrationNumber;
use Medzuch\DhlExpress\Dto\Shipment\UploadInvoiceDataRequest;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\InvoicePartyTypeCode;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use Medzuch\DhlExpress\Enum\RegistrationNumberTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use PHPUnit\Framework\TestCase;

final class UploadInvoiceDataRequestTest extends TestCase
{
    private function makeDeclaration(): ExportDeclaration
    {
        return new ExportDeclaration(
            lineItems: [
                new ExportLineItem(
                    number: 1,
                    description: 'Product',
                    price: 100.00,
                    quantity: new LineItemQuantity(1, LineItemQuantityUnit::PCS),
                    manufacturerCountry: 'CZ',
                    weight: new LineItemWeight(netValue: 1.0, grossValue: 1.2),
                ),
            ],
        );
    }

    public function testToArrayProducesExpectedShape(): void
    {
        $request = new UploadInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
        );

        $result = $request->toArray();

        self::assertArrayNotHasKey('plannedShipDate', $result);
        self::assertArrayNotHasKey('accounts', $result);
        self::assertArrayHasKey('content', $result);
        self::assertSame('EUR', $result['content']['currency']);
        self::assertSame('metric', $result['content']['unitOfMeasurement']);
        self::assertCount(1, $result['content']['exportDeclaration']);
    }

    public function testToArrayIncludesPlannedShipDateWhenProvided(): void
    {
        $request = new UploadInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'CZK',
            unitOfMeasurement: UnitSystem::Metric,
            plannedShipDate: new DateTimeImmutable('2026-05-20'),
        );

        $result = $request->toArray();

        self::assertSame('2026-05-20', $result['plannedShipDate']);
    }

    public function testToArrayIncludesAccountsWhenProvided(): void
    {
        $request = new UploadInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
        );

        $result = $request->toArray();

        self::assertCount(1, $result['accounts']);
        self::assertSame('shipper', $result['accounts'][0]['typeCode']);
    }

    public function testToArrayIncludesOutputImagePropertiesWhenProvided(): void
    {
        $request = new UploadInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            outputImageProperties: new InvoiceOutputImageProperties(
                imageOptions: [
                    new InvoiceImageOption(
                        templateName: 'COMMERCIAL_INVOICE_P_10',
                        isRequested: true,
                    ),
                ],
            ),
        );

        $result = $request->toArray();

        self::assertArrayHasKey('outputImageProperties', $result);
        self::assertCount(1, $result['outputImageProperties']['imageOptions']);
        self::assertSame('invoice', $result['outputImageProperties']['imageOptions'][0]['typeCode']);
        self::assertSame('COMMERCIAL_INVOICE_P_10', $result['outputImageProperties']['imageOptions'][0]['templateName']);
        self::assertTrue($result['outputImageProperties']['imageOptions'][0]['isRequested']);
    }

    public function testToArrayOmitsEmptyOutputImageProperties(): void
    {
        $request = new UploadInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            outputImageProperties: new InvoiceOutputImageProperties(),
        );

        $result = $request->toArray();

        self::assertArrayNotHasKey('outputImageProperties', $result);
    }

    public function testToArrayIncludesCustomerDetailsWhenProvided(): void
    {
        $request = new UploadInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            customerDetails: new InvoiceCustomerDetails(
                sellerDetails: new InvoiceParty(
                    contactAddress: $this->makeContactAddress(),
                    typeCode: InvoicePartyTypeCode::Business,
                    registrationNumbers: [
                        new RegistrationNumber(
                            typeCode: RegistrationNumberTypeCode::VAT,
                            number: 'CZ123456789',
                            issuerCountryCode: new CountryCode('CZ'),
                        ),
                    ],
                ),
                buyerDetails: new InvoiceParty(
                    contactAddress: $this->makeContactAddress(),
                ),
            ),
        );

        $result = $request->toArray();

        self::assertArrayHasKey('customerDetails', $result);
        self::assertArrayHasKey('sellerDetails', $result['customerDetails']);
        self::assertArrayHasKey('buyerDetails', $result['customerDetails']);
        self::assertSame('business', $result['customerDetails']['sellerDetails']['typeCode']);
        self::assertSame('VAT', $result['customerDetails']['sellerDetails']['registrationNumbers'][0]['typeCode']);
        self::assertSame('CZ123456789', $result['customerDetails']['sellerDetails']['registrationNumbers'][0]['number']);
        self::assertSame('CZ', $result['customerDetails']['sellerDetails']['registrationNumbers'][0]['issuerCountryCode']);
        self::assertArrayNotHasKey('typeCode', $result['customerDetails']['buyerDetails']);
        self::assertArrayNotHasKey('registrationNumbers', $result['customerDetails']['buyerDetails']);
    }

    public function testToArrayOmitsEmptyCustomerDetails(): void
    {
        $request = new UploadInvoiceDataRequest(
            exportDeclarations: [$this->makeDeclaration()],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
            customerDetails: new InvoiceCustomerDetails(),
        );

        $result = $request->toArray();

        self::assertArrayNotHasKey('customerDetails', $result);
    }

    private function makeContactAddress(): ContactAddress
    {
        return new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('11000'),
            cityName: 'Prague',
            addressLine1: 'Václavské náměstí 1',
            phone: new PhoneNumber('+420123456789'),
            companyName: 'Acme s.r.o.',
            fullName: 'Jan Novák',
        );
    }
}

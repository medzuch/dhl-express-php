<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Dto\Shipment\UploadInvoiceDataRequest;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
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
}

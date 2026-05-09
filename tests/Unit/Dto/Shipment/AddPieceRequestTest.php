<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\AddPieceOutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\AddPiecePackage;
use Medzuch\DhlExpress\Dto\Shipment\AddPieceRequest;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use PHPUnit\Framework\TestCase;

final class AddPieceRequestTest extends TestCase
{
    public function testToArrayProducesCorrectStructure(): void
    {
        $date = new DateTimeImmutable('2026-06-01');
        $account = new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'));
        $package = new AddPiecePackage(weight: 1.0);

        $request = new AddPieceRequest(
            originalPlannedShippingDate: $date,
            productCode: 'P',
            accounts: [$account],
            packages: [$package],
        );

        $array = $request->toArray();

        self::assertSame('2026-06-01', $array['originalPlannedShippingDate']);
        self::assertSame('P', $array['productCode']);
        self::assertCount(1, $array['accounts']);
        self::assertSame('shipper', $array['accounts'][0]['typeCode']);
        self::assertArrayHasKey('content', $array);
        self::assertArrayHasKey('packages', $array['content']);
        self::assertCount(1, $array['content']['packages']);
    }

    public function testOptionalFieldsOmittedWhenNull(): void
    {
        $request = new AddPieceRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-06-01'),
            productCode: 'P',
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            packages: [new AddPiecePackage(weight: 1.0)],
        );

        $array = $request->toArray();

        self::assertArrayNotHasKey('outputImageProperties', $array);
        self::assertArrayNotHasKey('getRateEstimates', $array);
    }

    public function testOriginalPlannedShippingDateSerializesAsYYYYMMDD(): void
    {
        $request = new AddPieceRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-12-25'),
            productCode: 'N',
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('987654321'))],
            packages: [new AddPiecePackage(weight: 0.5)],
        );

        $array = $request->toArray();

        self::assertSame('2026-12-25', $array['originalPlannedShippingDate']);
    }

    public function testAccountsAtRootLevel(): void
    {
        $account = new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'));
        $request = new AddPieceRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-06-01'),
            productCode: 'P',
            accounts: [$account],
            packages: [new AddPiecePackage(weight: 1.0)],
        );

        $array = $request->toArray();

        self::assertArrayHasKey('accounts', $array);
        self::assertArrayNotHasKey('accounts', $array['content'] ?? []);
    }

    public function testOutputImagePropertiesIncludedWhenSet(): void
    {
        $outputImageProperties = new AddPieceOutputImageProperties(
            encodingFormat: LabelEncodingFormat::Pdf,
        );

        $request = new AddPieceRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-06-01'),
            productCode: 'P',
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            packages: [new AddPiecePackage(weight: 1.0)],
            outputImageProperties: $outputImageProperties,
        );

        $array = $request->toArray();

        self::assertArrayHasKey('outputImageProperties', $array);
        self::assertSame('pdf', $array['outputImageProperties']['encodingFormat']);
    }

    public function testGetRateEstimatesIncludedWhenSet(): void
    {
        $request = new AddPieceRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-06-01'),
            productCode: 'P',
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            packages: [new AddPiecePackage(weight: 1.0)],
            getRateEstimates: true,
        );

        $array = $request->toArray();

        self::assertTrue($array['getRateEstimates']);
    }
}

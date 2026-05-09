<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\DocumentImage;
use Medzuch\DhlExpress\Dto\Shipment\UploadImageRequest;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DocumentImageFormat;
use Medzuch\DhlExpress\Enum\DocumentImageTypeCode;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use PHPUnit\Framework\TestCase;

final class UploadImageRequestTest extends TestCase
{
    public function testToArrayProducesExpectedShape(): void
    {
        $request = new UploadImageRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-05-15'),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            productCode: 'P',
            documentImages: [
                new DocumentImage(
                    content: base64_encode('fake-pdf-content'),
                    typeCode: DocumentImageTypeCode::INV,
                    imageFormat: DocumentImageFormat::PDF,
                ),
            ],
        );

        $result = $request->toArray();

        self::assertSame('2026-05-15', $result['originalPlannedShippingDate']);
        self::assertSame('P', $result['productCode']);
        self::assertCount(1, $result['accounts']);
        self::assertSame('shipper', $result['accounts'][0]['typeCode']);
        self::assertCount(1, $result['documentImages']);
        self::assertSame('INV', $result['documentImages'][0]['typeCode']);
        self::assertSame('PDF', $result['documentImages'][0]['imageFormat']);
        self::assertSame(base64_encode('fake-pdf-content'), $result['documentImages'][0]['content']);
    }

    public function testDateFormattedAsYmd(): void
    {
        $request = new UploadImageRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-12-31'),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            productCode: 'P',
            documentImages: [
                new DocumentImage(content: 'abc', typeCode: DocumentImageTypeCode::AWB, imageFormat: DocumentImageFormat::PDF),
            ],
        );

        self::assertSame('2026-12-31', $request->toArray()['originalPlannedShippingDate']);
    }
}

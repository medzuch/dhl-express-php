<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ImageOptionTypeCode;
use PHPUnit\Framework\TestCase;

final class ImageOptionTypeCodeTest extends TestCase
{
    public function testAllSixValuesExist(): void
    {
        self::assertCount(6, ImageOptionTypeCode::cases());
    }

    public function testLabelWireValue(): void
    {
        self::assertSame('label', ImageOptionTypeCode::Label->value);
    }

    public function testWaybillDocWireValue(): void
    {
        self::assertSame('waybillDoc', ImageOptionTypeCode::WaybillDoc->value);
    }

    public function testInvoiceWireValue(): void
    {
        self::assertSame('invoice', ImageOptionTypeCode::Invoice->value);
    }

    public function testQrCodeWireValue(): void
    {
        self::assertSame('qr-code', ImageOptionTypeCode::QrCode->value);
    }

    public function testShipmentReceiptWireValue(): void
    {
        self::assertSame('shipmentReceipt', ImageOptionTypeCode::ShipmentReceipt->value);
    }

    public function testReceiptWireValue(): void
    {
        self::assertSame('receipt', ImageOptionTypeCode::Receipt->value);
    }

    public function testFromCanResolveQrCode(): void
    {
        self::assertSame(ImageOptionTypeCode::QrCode, ImageOptionTypeCode::from('qr-code'));
    }
}

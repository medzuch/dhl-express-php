<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\CreateShipmentResponseHydrator;
use PHPUnit\Framework\TestCase;

final class CreateShipmentResponseHydratorTest extends TestCase
{
    public function testInvoiceDocumentExposesInvoiceNumber(): void
    {
        $response = (new CreateShipmentResponseHydrator())->hydrate($this->loadFixture());

        $invoice = $response->documents[2];
        self::assertSame('invoice', $invoice->typeCode);
        self::assertSame('INV-1', $invoice->invoiceNumber);
    }

    public function testDocumentWithoutInvoiceNumberReportsNone(): void
    {
        $response = (new CreateShipmentResponseHydrator())->hydrate($this->loadFixture());

        self::assertSame('label', $response->documents[0]->typeCode);
        self::assertNull($response->documents[0]->invoiceNumber);
        self::assertNull($response->packages[0]->documents[0]->invoiceNumber);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadFixture(): array
    {
        $contents = file_get_contents(__DIR__ . '/../../../Fixtures/shipments/create-shipment-success.json');
        if ($contents === false) {
            self::fail('Failed to load create-shipment-success.json');
        }
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}

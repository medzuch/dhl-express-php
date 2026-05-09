<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\DocumentImageResult;
use Medzuch\DhlExpress\Dto\Shipment\GetImageResponse;
use Medzuch\DhlExpress\Dto\Shipment\GetImageResponseHydrator;
use PHPUnit\Framework\TestCase;

final class GetImageResponseTest extends TestCase
{
    public function testHydratorBuildsResponseFromArray(): void
    {
        $hydrator = new GetImageResponseHydrator();

        $body = [
            'documents' => [
                [
                    'shipmentTrackingNumber' => '1234567890',
                    'typeCode' => 'INV',
                    'encodingFormat' => 'PDF',
                    'content' => 'JVBERi0xLjQ=',
                    'function' => 'export',
                ],
            ],
        ];

        $response = $hydrator->hydrate($body);

        self::assertInstanceOf(GetImageResponse::class, $response);
        self::assertCount(1, $response->documents);

        $doc = $response->documents[0];
        self::assertInstanceOf(DocumentImageResult::class, $doc);
        self::assertSame('1234567890', $doc->shipmentTrackingNumber);
        self::assertSame('INV', $doc->typeCode);
        self::assertSame('PDF', $doc->encodingFormat);
        self::assertSame('JVBERi0xLjQ=', $doc->content);
        self::assertSame('export', $doc->function);
    }

    public function testHydratorHandlesMissingOptionalFunction(): void
    {
        $hydrator = new GetImageResponseHydrator();

        $body = [
            'documents' => [
                [
                    'shipmentTrackingNumber' => '9876543210',
                    'typeCode' => 'AWB',
                    'encodingFormat' => 'PDF',
                    'content' => 'abc123',
                ],
            ],
        ];

        $response = $hydrator->hydrate($body);

        self::assertNull($response->documents[0]->function);
    }

    public function testHydratorHandlesEmptyDocuments(): void
    {
        $hydrator = new GetImageResponseHydrator();

        $response = $hydrator->hydrate(['documents' => []]);

        self::assertCount(0, $response->documents);
    }
}

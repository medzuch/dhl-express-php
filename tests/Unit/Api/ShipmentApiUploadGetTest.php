<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use DateTimeImmutable;
use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\ShipmentApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\DocumentImage;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\GetImageRequest;
use Medzuch\DhlExpress\Dto\Shipment\GetImageResponse;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Dto\Shipment\UploadImageRequest;
use Medzuch\DhlExpress\Dto\Shipment\UploadInvoiceDataRequest;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\DocumentImageFormat;
use Medzuch\DhlExpress\Enum\DocumentImageTypeCode;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class ShipmentApiUploadGetTest extends TestCase
{
    public function testUploadImageSendsPatchRequestToCorrectEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream('{}')),
        );

        $api = $this->makeApi($mockClient, $factory);

        $api->uploadImage('1234567890', new UploadImageRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-05-15'),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
            productCode: 'P',
            documentImages: [
                new DocumentImage(
                    content: base64_encode('fake-pdf'),
                    typeCode: DocumentImageTypeCode::INV,
                    imageFormat: DocumentImageFormat::PDF,
                ),
            ],
        ));

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('PATCH', $sent->getMethod());
        self::assertStringContainsString('/shipments/1234567890/upload-image', (string) $sent->getUri());
    }

    public function testUploadImageSendsCorrectJsonBody(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200)->withBody($factory->createStream('{}')));

        $api = $this->makeApi($mockClient, $factory);

        $api->uploadImage('AWB123', new UploadImageRequest(
            originalPlannedShippingDate: new DateTimeImmutable('2026-05-20'),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('987654321'))],
            productCode: 'N',
            documentImages: [
                new DocumentImage(content: 'data', typeCode: DocumentImageTypeCode::COO, imageFormat: DocumentImageFormat::PDF),
            ],
        ));

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $body = json_decode((string) $sent->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertSame('2026-05-20', $body['originalPlannedShippingDate']);
        self::assertSame('N', $body['productCode']);
        self::assertSame('COO', $body['documentImages'][0]['typeCode']);
    }

    public function testUploadInvoiceDataSendsPatchRequestToCorrectEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200)->withBody($factory->createStream('{}')));

        $api = $this->makeApi($mockClient, $factory);

        $api->uploadInvoiceData('1234567890', $this->makeUploadInvoiceDataRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('PATCH', $sent->getMethod());
        self::assertStringContainsString('/shipments/1234567890/upload-invoice-data', (string) $sent->getUri());
    }

    public function testUploadInvoiceDataSendsCorrectJsonBody(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200)->withBody($factory->createStream('{}')));

        $api = $this->makeApi($mockClient, $factory);

        $api->uploadInvoiceData('AWB456', $this->makeUploadInvoiceDataRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $body = json_decode((string) $sent->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertArrayHasKey('content', $body);
        self::assertSame('EUR', $body['content']['currency']);
        self::assertSame('metric', $body['content']['unitOfMeasurement']);
        self::assertCount(1, $body['content']['exportDeclaration']);
    }

    public function testGetImageSendsGetRequestToCorrectEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream($this->loadFixture('get_image_response.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $response = $api->getImage('1234567890', new GetImageRequest(shipperAccountNumber: '123456789'));

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        self::assertStringContainsString('/shipments/1234567890/get-image', (string) $sent->getUri());
        self::assertStringContainsString('shipperAccountNumber=123456789', (string) $sent->getUri());

        self::assertInstanceOf(GetImageResponse::class, $response);
        self::assertCount(1, $response->documents);
        self::assertSame('1234567890', $response->documents[0]->shipmentTrackingNumber);
        self::assertSame('INV', $response->documents[0]->typeCode);
    }

    public function testGetImageSerializesTypeCodesAsRepeatedTypeCodeParam(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody($factory->createStream($this->loadFixture('get_image_response.json'))),
        );

        $api = $this->makeApi($mockClient, $factory);

        $api->getImage('1234567890', new GetImageRequest(
            shipperAccountNumber: '123456789',
            typeCodes: ['waybill', 'commercial-invoice'],
        ));

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $uri = (string) $sent->getUri();
        // DHL expects repeated `typeCode` (singular) params, not `typeCodes`.
        self::assertStringContainsString('typeCode=waybill', $uri);
        self::assertStringContainsString('typeCode=commercial-invoice', $uri);
        self::assertStringNotContainsString('typeCodes', $uri);
    }

    public function testGetImageRequestRequiresAtLeastOneAccountNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetImageRequest();
    }

    private function makeUploadInvoiceDataRequest(): UploadInvoiceDataRequest
    {
        return new UploadInvoiceDataRequest(
            exportDeclarations: [
                new ExportDeclaration(
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
                ),
            ],
            currency: 'EUR',
            unitOfMeasurement: UnitSystem::Metric,
        );
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): ShipmentApi
    {
        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
        );
        $generator = new class () implements MessageReferenceGenerator {
            public function generate(): MessageReference
            {
                return new MessageReference('11111111-2222-4333-8444-555555555555');
            }
        };
        $builder = new RequestBuilder($factory, $factory, $generator, $config);
        $transport = new HttpTransport($mockClient, new ResponseParser(new DhlErrorMapper()));

        return new ShipmentApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/shipments/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

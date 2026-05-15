<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use Http\Mock\Client as MockClient;
use Medzuch\DhlExpress\Api\InvoiceApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Invoice\UploadStandaloneInvoiceDataRequest;
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Exception\DhlErrorMapper;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Http\ResponseParser;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class InvoiceApiTest extends TestCase
{
    public function testUploadInvoiceDataSendsPostRequestToCorrectEndpoint(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200)->withBody($factory->createStream('{}')));

        $api = $this->makeApi($mockClient, $factory);

        $api->uploadInvoiceData($this->makeRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertStringContainsString('/invoices/upload-invoice-data', (string) $sent->getUri());
    }

    public function testUploadInvoiceDataSendsCorrectJsonBody(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse($factory->createResponse(200)->withBody($factory->createStream('{}')));

        $api = $this->makeApi($mockClient, $factory);

        $api->uploadInvoiceData($this->makeRequest());

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        $body = json_decode((string) $sent->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($body);
        self::assertSame('1234567890', $body['shipmentTrackingNumber']);
        self::assertSame('EUR', $body['content']['currency']);
        self::assertSame('metric', $body['content']['unitOfMeasurement']);
        self::assertCount(1, $body['accounts']);
        self::assertSame('shipper', $body['accounts'][0]['typeCode']);
    }

    private function makeRequest(): UploadStandaloneInvoiceDataRequest
    {
        return new UploadStandaloneInvoiceDataRequest(
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
            shipmentTrackingNumber: new TrackingNumber('1234567890'),
            accounts: [new Account(AccountTypeCode::Shipper, new AccountNumber('123456789'))],
        );
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): InvoiceApi
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

        return new InvoiceApi($builder, $transport);
    }
}

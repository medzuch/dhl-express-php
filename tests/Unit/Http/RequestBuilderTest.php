<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Http;

use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\ValueObject\IntegrationProfile;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use Medzuch\DhlExpress\ValueObject\PlatformIdentifier;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class RequestBuilderTest extends TestCase
{
    public const FIXED_REFERENCE = '11111111-2222-4333-8444-555555555555';

    public function testBuildsRequestWithCorrectMethodAndAbsoluteUrl(): void
    {
        $request = $this->buildBuilder()->build('GET', '/shipments/9356579890/tracking');

        self::assertSame('GET', $request->getMethod());
        self::assertSame(
            'https://express.api.dhl.com/mydhlapi/test/shipments/9356579890/tracking',
            (string) $request->getUri(),
        );
    }

    public function testIncludesStandardHeaders(): void
    {
        $request = $this->buildBuilder()->build('GET', '/shipments/9356579890/tracking');

        self::assertSame('Basic ' . base64_encode('user:pass'), $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertSame('eng', $request->getHeaderLine('Accept-Language'));
        self::assertSame(self::FIXED_REFERENCE, $request->getHeaderLine('Message-Reference'));
        self::assertSame('3.2.0', $request->getHeaderLine('x-version'));
    }

    public function testOmitsThreePartyHeadersWhenIntegrationProfileIsNull(): void
    {
        $request = $this->buildBuilder()->build('GET', '/shipments/9356579890/tracking');

        self::assertFalse($request->hasHeader('Plugin-Name'));
        self::assertFalse($request->hasHeader('Plugin-Version'));
        self::assertFalse($request->hasHeader('Shipping-System-Platform-Name'));
        self::assertFalse($request->hasHeader('Shipping-System-Platform-Version'));
        self::assertFalse($request->hasHeader('Webstore-Platform-Name'));
        self::assertFalse($request->hasHeader('Webstore-Platform-Version'));
    }

    public function testIncludesPluginHeadersWhenProfileSetsPluginSlot(): void
    {
        $profile = new IntegrationProfile(
            plugin: new PlatformIdentifier(name: 'AcmePlugin', version: '1.4'),
        );

        $request = $this->buildBuilder($profile)->build('GET', '/shipments/9356579890/tracking');

        self::assertSame('AcmePlugin', $request->getHeaderLine('Plugin-Name'));
        self::assertSame('1.4', $request->getHeaderLine('Plugin-Version'));
        self::assertFalse($request->hasHeader('Shipping-System-Platform-Name'));
        self::assertFalse($request->hasHeader('Webstore-Platform-Name'));
    }

    public function testIncludesShippingSystemHeadersWhenProfileSetsShippingSystemSlot(): void
    {
        $profile = new IntegrationProfile(
            shippingSystem: new PlatformIdentifier(name: 'AcmeShipper', version: '2.4'),
        );

        $request = $this->buildBuilder($profile)->build('GET', '/shipments/9356579890/tracking');

        self::assertSame('AcmeShipper', $request->getHeaderLine('Shipping-System-Platform-Name'));
        self::assertSame('2.4', $request->getHeaderLine('Shipping-System-Platform-Version'));
        self::assertFalse($request->hasHeader('Plugin-Name'));
        self::assertFalse($request->hasHeader('Webstore-Platform-Name'));
    }

    public function testIncludesWebstoreHeadersWhenProfileSetsWebstoreSlot(): void
    {
        $profile = new IntegrationProfile(
            webstore: new PlatformIdentifier(name: 'AcmeStore', version: '3.1'),
        );

        $request = $this->buildBuilder($profile)->build('GET', '/shipments/9356579890/tracking');

        self::assertSame('AcmeStore', $request->getHeaderLine('Webstore-Platform-Name'));
        self::assertSame('3.1', $request->getHeaderLine('Webstore-Platform-Version'));
        self::assertFalse($request->hasHeader('Plugin-Name'));
        self::assertFalse($request->hasHeader('Shipping-System-Platform-Name'));
    }

    public function testAppendsQueryParametersToUri(): void
    {
        $request = $this->buildBuilder()->build(
            'GET',
            '/tracking',
            queryParams: ['shipmentTrackingNumber' => '9356579890', 'trackingView' => 'all-checkpoints'],
        );

        self::assertSame(
            'https://express.api.dhl.com/mydhlapi/test/tracking?shipmentTrackingNumber=9356579890&trackingView=all-checkpoints',
            (string) $request->getUri(),
        );
    }

    public function testEncodesQueryParameterValues(): void
    {
        $request = $this->buildBuilder()->build(
            'GET',
            '/address-validate',
            queryParams: ['city' => 'New York', 'postalCode' => 'NY 10001'],
        );

        self::assertStringContainsString('city=New%20York', (string) $request->getUri());
        self::assertStringContainsString('postalCode=NY%2010001', (string) $request->getUri());
    }

    public function testAttachesJsonBodyForPostRequests(): void
    {
        $body = ['plannedShippingDateAndTime' => '2026-05-04T12:00:00 GMT+00:00'];

        $request = $this->buildBuilder()->build(
            'POST',
            '/shipments',
            jsonBody: $body,
        );

        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame(
            '{"plannedShippingDateAndTime":"2026-05-04T12:00:00 GMT+00:00"}',
            (string) $request->getBody(),
        );
    }

    public function testGetRequestsHaveEmptyBody(): void
    {
        $request = $this->buildBuilder()->build('GET', '/shipments/9356579890/tracking');

        self::assertSame('', (string) $request->getBody());
        self::assertFalse($request->hasHeader('Content-Type'));
    }

    public function testEachInvocationReceivesAFreshMessageReference(): void
    {
        $references = [
            new MessageReference('a1111111-2222-4333-8444-555555555555'),
            new MessageReference('b1111111-2222-4333-8444-555555555555'),
        ];
        $generator = new class ($references) implements MessageReferenceGenerator {
            /**
             * @param list<MessageReference> $queue
             */
            public function __construct(private array $queue) {}

            public function generate(): MessageReference
            {
                $value = array_shift($this->queue);
                if ($value === null) {
                    throw new \RuntimeException('queue exhausted');
                }

                return $value;
            }
        };
        $factory = new Psr17Factory();
        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
        );
        $builder = new RequestBuilder($factory, $factory, $generator, $config);

        $first = $builder->build('GET', '/shipments/9356579890/tracking');
        $second = $builder->build('GET', '/shipments/9356579890/tracking');

        self::assertSame('a1111111-2222-4333-8444-555555555555', $first->getHeaderLine('Message-Reference'));
        self::assertSame('b1111111-2222-4333-8444-555555555555', $second->getHeaderLine('Message-Reference'));
    }

    public function testProducesPsrRequestInterfaceInstance(): void
    {
        $request = $this->buildBuilder()->build('GET', '/shipments/9356579890/tracking');

        self::assertInstanceOf(RequestInterface::class, $request);
    }

    private function buildBuilder(?IntegrationProfile $profile = null): RequestBuilder
    {
        $factory = new Psr17Factory();
        $generator = new class implements MessageReferenceGenerator {
            public function generate(): MessageReference
            {
                return new MessageReference(RequestBuilderTest::FIXED_REFERENCE);
            }
        };
        $config = new ClientConfig(
            environment: ApiEnvironment::Sandbox,
            credentials: new Credentials('user', 'pass'),
            integrationProfile: $profile,
        );

        return new RequestBuilder($factory, $factory, $generator, $config);
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Api;

use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Medzuch\DhlExpress\Api\IdentifierApi;
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\Dto\Identifier\IdentifierResponse;
use Medzuch\DhlExpress\Enum\ApiEnvironment;
use Medzuch\DhlExpress\Enum\IdentifierType;
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

final class IdentifierApiTest extends TestCase
{
    public function testHydratesAllocationResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('sid-allocation.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);

        $result = $api->allocate(
            new AccountNumber('123456789'),
            IdentifierType::SID,
            3,
        );

        self::assertInstanceOf(IdentifierResponse::class, $result);
        self::assertSame([], $result->warnings);
        self::assertCount(1, $result->identifiers);
        self::assertSame(IdentifierType::SID, $result->identifiers[0]->typeCode);
        self::assertSame(['1234567890', '1234567891', '1234567892'], $result->identifiers[0]->list);
    }

    public function testSendsCorrectRequest(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream($this->loadFixture('sid-allocation.json')),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $api->allocate(new AccountNumber('123456789'), IdentifierType::SID, 3);

        $sent = $mockClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);
        self::assertSame('GET', $sent->getMethod());
        $uri = (string) $sent->getUri();
        self::assertStringContainsString('/identifiers?', $uri);
        self::assertStringContainsString('accountNumber=123456789', $uri);
        self::assertStringContainsString('type=SID', $uri);
        self::assertStringContainsString('size=3', $uri);
    }

    public function testRejectsZeroOrNegativeSize(): void
    {
        $factory = new Psr17Factory();
        $api = $this->makeApi(new MockClient(), $factory);

        $this->expectException(InvalidArgumentException::class);

        $api->allocate(new AccountNumber('123456789'), IdentifierType::SID, 0);
    }

    public function testIgnoresUnknownTypeCodeInResponse(): void
    {
        $factory = new Psr17Factory();
        $mockClient = new MockClient();
        $mockClient->addResponse(
            $factory->createResponse(200)->withBody(
                $factory->createStream(
                    json_encode([
                        'identifiers' => [
                            ['typeCode' => 'UNKNOWN', 'list' => ['1']],
                            ['typeCode' => 'SID', 'list' => ['9876543210']],
                        ],
                    ], JSON_THROW_ON_ERROR),
                ),
            ),
        );

        $api = $this->makeApi($mockClient, $factory);
        $result = $api->allocate(new AccountNumber('123456789'), IdentifierType::SID, 1);

        self::assertCount(1, $result->identifiers);
        self::assertSame(IdentifierType::SID, $result->identifiers[0]->typeCode);
        self::assertSame(['9876543210'], $result->identifiers[0]->list);
    }

    private function makeApi(MockClient $mockClient, Psr17Factory $factory): IdentifierApi
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

        return new IdentifierApi($builder, $transport);
    }

    private function loadFixture(string $name): string
    {
        $path = __DIR__ . '/../../Fixtures/identifier/' . $name;
        $contents = file_get_contents($path);

        if ($contents === false) {
            self::fail("Failed to load fixture: {$path}");
        }

        return $contents;
    }
}

<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Pickup;

use Medzuch\DhlExpress\Dto\Pickup\PickupValueAddedService;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use PHPUnit\Framework\TestCase;

final class PickupValueAddedServiceTest extends TestCase
{
    public function testToArrayWithServiceCodeOnly(): void
    {
        $vas = new PickupValueAddedService(serviceCode: 'IB');

        self::assertSame(['serviceCode' => 'IB'], $vas->toArray());
    }

    public function testLocalServiceCodeSerializesWhenSet(): void
    {
        $vas = new PickupValueAddedService(
            serviceCode: 'IB',
            localServiceCode: 'CZ-LOCAL-01',
        );

        $payload = $vas->toArray();

        self::assertSame('IB', $payload['serviceCode']);
        self::assertSame('CZ-LOCAL-01', $payload['localServiceCode']);
    }

    public function testToArrayWithAllFields(): void
    {
        $vas = new PickupValueAddedService(
            serviceCode: 'IB',
            localServiceCode: 'CZ-LOCAL-01',
            value: 10.0,
            currency: new CurrencyCode('EUR'),
            method: 'cash',
        );

        self::assertSame(
            [
                'serviceCode' => 'IB',
                'localServiceCode' => 'CZ-LOCAL-01',
                'value' => 10.0,
                'currency' => 'EUR',
                'method' => 'cash',
            ],
            $vas->toArray(),
        );
    }

    public function testDoesNotExposeDangerousGoodsField(): void
    {
        // Smoke check — the pickup VAS schema has additionalProperties:false
        // and no dangerousGoods. Leaking it would cause DHL to 422 the request.
        $reflection = new \ReflectionClass(PickupValueAddedService::class);
        $params = $reflection->getConstructor()?->getParameters() ?? [];
        $names = array_map(static fn (\ReflectionParameter $p): string => $p->getName(), $params);

        self::assertNotContains('dangerousGoods', $names);
    }
}

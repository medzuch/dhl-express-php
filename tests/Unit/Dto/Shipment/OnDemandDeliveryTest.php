<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use Medzuch\DhlExpress\Dto\Shipment\OnDemandDelivery;
use Medzuch\DhlExpress\Enum\OnDemandDeliveryOption;
use PHPUnit\Framework\TestCase;

final class OnDemandDeliveryTest extends TestCase
{
    public function testServicePointPayloadHasNoRequestedDeliveryDate(): void
    {
        $delivery = new OnDemandDelivery(
            deliveryOption: OnDemandDeliveryOption::Servicepoint,
            servicePointId: 'SPL123',
        );

        $payload = $delivery->toArray();

        self::assertSame(['deliveryOption' => 'servicepoint', 'servicePointId' => 'SPL123'], $payload);
        self::assertArrayNotHasKey('requestedDeliveryDate', $payload);
    }

    public function testRequestedDeliveryDateIsNotAConstructorArgument(): void
    {
        $parameters = array_map(
            static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
            (new \ReflectionMethod(OnDemandDelivery::class, '__construct'))->getParameters(),
        );

        self::assertNotContains('requestedDeliveryDate', $parameters);
    }
}

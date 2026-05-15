<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Rate;

use Medzuch\DhlExpress\Dto\Rate\ValueAddedServiceFilter;
use Medzuch\DhlExpress\Enum\ValueAddedServiceMethod;
use Medzuch\DhlExpress\ValueObject\CurrencyCode;
use PHPUnit\Framework\TestCase;

final class ValueAddedServiceFilterTest extends TestCase
{
    public function testToArrayMinimalEmitsOnlyServiceCode(): void
    {
        $filter = new ValueAddedServiceFilter(serviceCode: 'II');

        self::assertSame(['serviceCode' => 'II'], $filter->toArray());
    }

    public function testToArrayWithAllFieldsIncludesMethod(): void
    {
        $filter = new ValueAddedServiceFilter(
            serviceCode: 'II',
            localServiceCode: 'IB',
            value: 100.0,
            currency: new CurrencyCode('GBP'),
            method: ValueAddedServiceMethod::Cash,
        );

        self::assertSame(
            [
                'serviceCode' => 'II',
                'localServiceCode' => 'IB',
                'value' => 100.0,
                'currency' => 'GBP',
                'method' => 'cash',
            ],
            $filter->toArray(),
        );
    }

    public function testToArrayNeverEmitsDgContent(): void
    {
        $filter = new ValueAddedServiceFilter(serviceCode: 'II');

        self::assertArrayNotHasKey('dgContent', $filter->toArray());
    }
}

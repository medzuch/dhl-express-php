<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Common;

use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use PHPUnit\Framework\TestCase;

final class RateAddressTest extends TestCase
{
    public function testToArrayEmitsRequiredFieldsOnly(): void
    {
        $address = new RateAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
        );

        self::assertSame(
            [
                'postalCode' => '14800',
                'cityName' => 'Prague',
                'countryCode' => 'CZ',
            ],
            $address->toArray(),
        );
    }

    public function testToArrayIncludesOptionalFieldsWhenSet(): void
    {
        $address = new RateAddress(
            countryCode: new CountryCode('SG'),
            postalCode: new PostalCode('048582'),
            cityName: 'SINGAPORE',
            addressLine1: 'Blk 6 Lock Rd',
            addressLine2: '02-10 Gillman Barracks',
            addressLine3: 'Barrack Street',
            countyName: 'Central',
        );

        $payload = $address->toArray();

        self::assertSame('Blk 6 Lock Rd', $payload['addressLine1']);
        self::assertSame('02-10 Gillman Barracks', $payload['addressLine2']);
        self::assertSame('Barrack Street', $payload['addressLine3']);
        self::assertSame('Central', $payload['countyName']);
    }

    public function testEmptyPostalCodeIsAllowed(): void
    {
        $address = new RateAddress(
            countryCode: new CountryCode('HK'),
            postalCode: new PostalCode(''),
            cityName: 'Hong Kong',
        );

        self::assertSame('', $address->toArray()['postalCode']);
    }
}

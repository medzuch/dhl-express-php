<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Products;

use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;
use PHPUnit\Framework\Attributes\Group;

/**
 * Sandbox checks for the /products endpoint.
 *
 * Requires `DHL_ACCOUNT_NUMBER` on top of the basic-auth env vars
 * — the products lookup uses the account's product entitlements
 * to scope the result.
 */
#[Group('integration')]
final class ProductsApiIntegrationTest extends IntegrationTestCase
{
    public function testListsProductsForCzechToUsShipment(): void
    {
        $client = $this->makeClient();
        $account = $this->requireAccountNumber();

        $response = $client->products()->list(
            account: $account,
            originCountryCode: new CountryCode('CZ'),
            destinationCountryCode: new CountryCode('US'),
            weight: new Weight(5.0, WeightUnit::KG),
            dimensions: new Dimensions(30.0, 20.0, 15.0, DimensionUnit::CM),
            plannedShippingDate: $this->nextBusinessDay(5),
            isCustomsDeclarable: true,
            unitOfMeasurement: UnitSystem::Metric,
            originPostalCode: new PostalCode('14800'),
            originCityName: 'Prague',
            destinationPostalCode: new PostalCode('10001'),
            destinationCityName: 'New York',
        );

        self::assertNotSame([], $response->products);
        // CZ → US should always include at least one Time Definite (TD) product.
        $networkTypes = array_map(static fn ($p): string => $p->networkTypeCode, $response->products);
        self::assertContains('TD', $networkTypes);
    }
}

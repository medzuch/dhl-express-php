<?php

declare(strict_types=1);

/**
 * Get DHL Express shipping rates for a CZ → DE parcel.
 *
 * Usage:
 *   DHL_API_KEY=x DHL_API_SECRET=y php examples/get-rates.php
 */

require_once __DIR__ . '/bootstrap.php';

use Medzuch\DhlExpress\Builder\RateRequestBuilder;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;

$client = makeClient();

try {
    $request = (new RateRequestBuilder())
        ->withShipper(new RateAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
        ))
        ->withReceiver(new RateAddress(
            countryCode: new CountryCode('DE'),
            postalCode: new PostalCode('10115'),
            cityName: 'Berlin',
        ))
        ->withPlannedShippingDate(new DateTimeImmutable('+2 days'))
        ->withUnitSystem(UnitSystem::Metric)
        ->withIsCustomsDeclarable(true)
        ->withPackage(new RatePackage(
            weight: new Weight(2.5, WeightUnit::KG),
            dimensions: new Dimensions(30.0, 20.0, 15.0, DimensionUnit::CM),
        ))
        ->build();

    $response = $client->rates()->quote($request);
} catch (InvalidRequestException $e) {
    fwrite(STDERR, "Validation error:\n");
    foreach ($e->errors() as ['field' => $field, 'message' => $msg]) {
        fwrite(STDERR, "  {$field}: {$msg}\n");
    }
    exit(1);
} catch (DhlApiException $e) {
    fwrite(STDERR, "DHL API error: {$e->getMessage()}\n");
    exit(1);
}

echo sprintf("Found %d products:\n\n", count($response->products));

foreach ($response->products as $product) {
    $price = $product->totalPrices[0] ?? null;
    $priceStr = $price !== null
        ? sprintf('%s %s', $price->price, $price->priceCurrency)
        : 'price unavailable';

    echo sprintf("  %-40s (%s)  %s\n", $product->productName, $product->productCode, $priceStr);
}

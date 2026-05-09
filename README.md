# medzuch/dhl-express-php

A clean, framework-agnostic PHP 8.3 library for the **DHL Express MyDHL API 3.2.2**.

[![CI](https://github.com/medzuch/dhl-express-php/actions/workflows/ci.yml/badge.svg)](https://github.com/medzuch/dhl-express-php/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/php-8.3%2B-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Features

| Domain | Operations |
|---|---|
| **Tracking** | Single shipment tracking, multi-shipment tracking |
| **Shipments** | Create shipment, upload PLT image, upload invoice data, add piece, get image |
| **Rates** | Single-piece rates (GET), multi-piece rates (POST) |
| **Landed Cost** | Estimate duties and taxes |
| **Pickups** | Create, update, cancel pickup bookings |
| **Products** | List available shipping products |
| **Address** | Validate destination addresses |
| **Service Points** | Find DHL service points |
| **EPOD** | Electronic proof of delivery |
| **Identifiers** | Resolve tracking identifiers |
| **Reference Data** | Look up DHL code lists |

---

## Requirements

- PHP **8.3+**
- `ext-json`

## Installation

```bash
composer require medzuch/dhl-express-php
```

The library ships with **Guzzle 7** as its default HTTP client. If you want to use a different PSR-18 compliant client, require it separately and inject it via the `DhlClient` constructor.

---

## Quick Start

```php
use Medzuch\DhlExpress\Auth\Credentials;
use Medzuch\DhlExpress\ClientConfig;
use Medzuch\DhlExpress\DhlClient;
use Medzuch\DhlExpress\Enum\ApiEnvironment;

$client = new DhlClient(new ClientConfig(
    environment: ApiEnvironment::Sandbox,   // or ApiEnvironment::Production
    credentials: new Credentials(
        username: getenv('DHL_API_KEY'),
        password: getenv('DHL_API_SECRET'),
    ),
));
```

---

## Usage Examples

### Track a Shipment

```php
use Medzuch\DhlExpress\ValueObject\TrackingNumber;

$response = $client->tracking()->getByTrackingNumber(new TrackingNumber('1234567890'));

echo $response->status . ': ' . $response->description . PHP_EOL;

foreach ($response->events as $event) {
    echo sprintf(
        '[%s %s] %s (%s)' . PHP_EOL,
        $event->date,
        $event->time,
        $event->description,
        $event->typeCode,
    );
}
```

> Use `tracking()->getMany(...)` to track up to 10 shipments in a single call. It returns a `list<TrackingResponse>` with the same flat shape per entry.

### Get Shipping Rates

```php
use Medzuch\DhlExpress\Builder\RateRequestBuilder;
use Medzuch\DhlExpress\Dto\Common\RateAddress;
use Medzuch\DhlExpress\Dto\Common\RatePackage;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;

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
    ->withPlannedShippingDate(new DateTimeImmutable('+1 day'))
    ->withUnitSystem(UnitSystem::Metric)
    ->withIsCustomsDeclarable(true)
    ->withPackage(new RatePackage(
        weight: new Weight(2.5, WeightUnit::KG),
        dimensions: new Dimensions(30.0, 20.0, 15.0, DimensionUnit::CM),
    ))
    ->build();

$response = $client->rates()->quote($request);

foreach ($response->products as $product) {
    $price = $product->totalPrices[0] ?? null;
    $priceStr = $price !== null
        ? sprintf('%s %s', $price->price, $price->priceCurrency)
        : 'price unavailable';

    echo sprintf('%s (%s): %s' . PHP_EOL, $product->productName, $product->productCode, $priceStr);
}
```

### Create a Shipment

```php
use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
use Medzuch\DhlExpress\Dto\Shipment\OutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;

$request = (new CreateShipmentBuilder())
    ->withShipper(new ContactAddress(
        countryCode: new CountryCode('CZ'),
        postalCode: new PostalCode('14800'),
        cityName: 'Prague',
        addressLine1: 'Vaclavske namesti 1',
        phone: new PhoneNumber('+420 222 333 444'),
        companyName: 'Acme s.r.o.',
        fullName: 'Jan Novak',
    ))
    ->withReceiver(new ContactAddress(
        countryCode: new CountryCode('CZ'),
        postalCode: new PostalCode('60200'),
        cityName: 'Brno',
        addressLine1: 'Namesti Svobody 10',
        phone: new PhoneNumber('+420 555 666 777'),
        companyName: 'Receiver s.r.o.',
        fullName: 'Petr Dvorak',
    ))
    ->withPlannedShippingDate(new DateTimeImmutable('+1 day'))
    ->withProductCode('N')
    ->withPickupRequested(false)
    ->withIsCustomsDeclarable(false)
    ->withContentDescription('Books')
    ->withUnitSystem(UnitSystem::Metric)
    ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
    ->withPackage(new Package(
        weight: new Weight(1.0, WeightUnit::KG),
        dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
    ))
    ->withOutputImageProperties(new OutputImageProperties(
        encodingFormat: LabelEncodingFormat::Pdf,
        imageOptions: [new ImageOption(typeCode: 'label', isRequested: true)],
    ))
    ->build();

$response = $client->shipments()->create($request);

echo 'Tracking number: ' . $response->shipmentTrackingNumber . PHP_EOL;
echo 'Dispatch confirmation: ' . $response->dispatchConfirmationNumber . PHP_EOL;

// Save the PDF label
foreach ($response->documents as $doc) {
    if ($doc->typeCode === 'label') {
        file_put_contents('label.pdf', base64_decode($doc->content));
    }
}
```

### Cross-border Shipment with Customs Declaration

```php
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;

$request = (new CreateShipmentBuilder())
    ->withShipper(/* ContactAddress */)
    ->withReceiver(/* ContactAddress */)
    ->withPlannedShippingDate(new DateTimeImmutable('+1 day'))
    ->withProductCode('P')
    ->withPickupRequested(false)
    ->withIsCustomsDeclarable(true)
    ->withContentDescription('Electronics')
    ->withUnitSystem(UnitSystem::Metric)
    ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
    ->withPackage(new Package(weight: new Weight(1.5, WeightUnit::KG)))
    ->withExportDeclaration(new ExportDeclaration(
        lineItems: [
            new ExportLineItem(
                number: 1,
                description: 'Laptop computer',
                price: 800.00,
                quantity: new LineItemQuantity(1, LineItemQuantityUnit::PCS),
                manufacturerCountry: 'CZ',
                weight: new LineItemWeight(netValue: 1.5, grossValue: 1.8),
            ),
        ],
    ))
    ->withDeclaredValue(800.00, 'EUR')
    ->build();
```

### Book a Pickup

```php
use Medzuch\DhlExpress\Builder\PickupRequestBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Pickup\PickupPackage;
use Medzuch\DhlExpress\Dto\Pickup\PickupShipmentDetails;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;

$request = (new PickupRequestBuilder())
    ->withShipper(new ContactAddress(
        countryCode: new CountryCode('CZ'),
        postalCode: new PostalCode('14800'),
        cityName: 'Prague',
        addressLine1: 'Vaclavske namesti 1',
        phone: new PhoneNumber('+420 222 333 444'),
        companyName: 'Acme s.r.o.',
        fullName: 'Jan Novak',
    ))
    ->withPlannedPickupDateTime(new DateTimeImmutable('+1 day 13:00:00'))
    ->withCloseTime('18:00')
    ->withLocation('reception')
    ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
    ->withShipmentDetails(new PickupShipmentDetails(
        productCode: 'N',
        isCustomsDeclarable: false,
        unitOfMeasurement: UnitSystem::Metric,
        packages: [new PickupPackage(weight: new Weight(1.0, WeightUnit::KG))],
    ))
    ->build();

$response = $client->pickups()->create($request);

$confirmationNumber = $response->dispatchConfirmationNumbers[0];
echo 'Pickup booked: ' . $confirmationNumber . PHP_EOL;

// Cancel later:
// $client->pickups()->cancel($confirmationNumber, 'Jan Novak', 'Order cancelled');
```

---

## Error Handling

All exceptions extend `DhlException`. Catch the specific type you care about:

```php
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Exception\DhlValidationException;
use Medzuch\DhlExpress\Exception\InvalidRequestException;

try {
    $response = $client->shipments()->create($request);
} catch (InvalidRequestException $e) {
    // Builder cross-field validation failed — no network call was made
    foreach ($e->errors() as ['field' => $field, 'message' => $msg]) {
        echo "{$field}: {$msg}" . PHP_EOL;
    }
} catch (DhlValidationException $e) {
    // DHL returned 400/422 — request rejected by the API
    echo 'DHL validation error: ' . $e->getMessage() . PHP_EOL;
    echo 'DHL error code: ' . $e->dhlErrorCode . PHP_EOL;
} catch (DhlApiException $e) {
    // Any other DHL-side error (5xx, auth failure, etc.)
    echo 'API error: ' . $e->getMessage() . PHP_EOL;
} catch (DhlNetworkException $e) {
    // Transport-level failure (DNS, timeout, SSL)
    echo 'Network error: ' . $e->getMessage() . PHP_EOL;
}
```

---

## Configuration

### Custom HTTP Client

Any PSR-18 compliant client can be injected:

```php
$client = new DhlClient(
    config: $config,
    httpClient: $myPsr18Client,
    requestFactory: $myPsr17RequestFactory,
    streamFactory: $myPsr17StreamFactory,
);
```

### Logging

Pass any PSR-3 logger to capture request and response payloads at `debug` level. The `Authorization` header is automatically redacted:

```php
$client = new DhlClient($config, logger: $myPsr3Logger);
```

### Timeout

```php
$config = new ClientConfig(
    environment: ApiEnvironment::Production,
    credentials: $credentials,
    timeout: 30,  // seconds, default 10
);
```

---

## Development

### Setup

```bash
git clone git@github.com:medzuch/dhl-express-php.git
cd dhl-express-php
make build
make install
```

### Make Targets

| Command | Description |
|---|---|
| `make test` | Run PHPUnit unit suite |
| `make test-integration` | Run integration tests (requires env vars below) |
| `make analyse` | PHPStan on `src/` (level max) |
| `make analyse-all` | PHPStan on `src/` and `tests/` |
| `make cs-fix` | Fix code style |
| `make cs-check` | Check style without modifying |
| `make check` | Tests + full analysis + style check |
| `make shell` | Shell inside container |

### Integration Tests

```bash
export DHL_API_KEY=your_api_key
export DHL_API_SECRET=your_api_secret
export DHL_ACCOUNT_NUMBER=your_account_number

make test-integration
```

---

## License

MIT — see [LICENSE](LICENSE).

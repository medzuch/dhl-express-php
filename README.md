# medzuch/dhl-express-php

A clean, framework-agnostic PHP 8.3 library for the **DHL Express MyDHL API 3.4.0** (`x-version: 3.4.0`).

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
| **Early Shipment Screening** | BBX Denied Party screening (`POST /early-shipment-screening`) |

---

## Requirements

- PHP **8.3+**
- `ext-json`

## Installation

```bash
composer require medzuch/dhl-express-php guzzlehttp/guzzle
```

The library uses **[php-http/discovery](https://github.com/php-http/discovery)** to auto-detect a PSR-18 HTTP client and PSR-17 factories at runtime. Any PSR-18 compliant client works — Guzzle 7 is the recommended default.

If you want to use a different client (e.g. Symfony HttpClient), require it instead and it will be discovered automatically. You can also inject any PSR-18/PSR-17 implementation directly via the `DhlClient` constructor.

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

> Use `tracking()->getMany(...)` to track up to 200 shipments in a single call. It returns a `list<TrackingResponse>` with the same flat shape per entry.

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
use Medzuch\DhlExpress\Dto\Shipment\Pickup;
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
    ->withPickup(new Pickup(isRequested: false))
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

MyDHL API 3.4.0 sends export declarations as a list (`content.exportDeclarations`, 1–100 per shipment; DHL's account default limit is 5). Each declaration needs line items, an invoice and an incoterm. A declaration without its own incoterm inherits the shipment-level one set with `withIncoterm()`. Invoice numbers must be unique across declarations.

```php
use Medzuch\DhlExpress\Dto\Shipment\ExportDeclaration;
use Medzuch\DhlExpress\Dto\Shipment\ExportInvoice;
use Medzuch\DhlExpress\Dto\Shipment\ExportLineItem;
use Medzuch\DhlExpress\Dto\Shipment\LineItemQuantity;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\LineItemQuantityUnit;

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
        countryCode: new CountryCode('US'),
        postalCode: new PostalCode('10001'),
        cityName: 'New York',
        addressLine1: '350 Fifth Avenue',
        phone: new PhoneNumber('+1 212 555 0100'),
        companyName: 'Receiver Inc.',
        fullName: 'John Smith',
    ))
    ->withPlannedShippingDate(new DateTimeImmutable('+1 day'))
    ->withProductCode('P')
    ->withPickup(new Pickup(isRequested: false))
    ->withIsCustomsDeclarable(true)
    ->withContentDescription('Electronics')
    ->withUnitSystem(UnitSystem::Metric)
    ->withIncoterm(Incoterm::DAP) // default for declarations without their own incoterm
    ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber('123456789')))
    ->withPackage(new Package(weight: new Weight(2.5, WeightUnit::KG)))
    // Inherits DAP; the invoice is signed so DHL can print a signed paperless-trade invoice.
    ->addExportDeclaration(new ExportDeclaration(
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
        invoice: new ExportInvoice(
            number: 'INV-2026-001',
            date: new DateTimeImmutable('today'),
            signatureName: 'Jan Novak',
            signatureTitle: 'Export Manager',
            signatureImage: base64_encode('...signature PNG bytes...'),
        ),
    ))
    // Explicit incoterm on the declaration wins over the shipment-level default.
    ->addExportDeclaration(new ExportDeclaration(
        lineItems: [
            new ExportLineItem(
                number: 1,
                description: 'Laptop bag',
                price: 50.00,
                quantity: new LineItemQuantity(2, LineItemQuantityUnit::PCS),
                manufacturerCountry: 'CZ',
                weight: new LineItemWeight(netValue: 0.4),
            ),
        ],
        invoice: new ExportInvoice(number: 'INV-2026-002', date: new DateTimeImmutable('today')),
        incoterm: Incoterm::EXW,
    ))
    ->withDeclaredValue(900.00, 'EUR') // must equal Σ price × quantity across all declarations
    ->build();
```

> Create Shipment never sends `invoice.function` (its schema rejects it). The invoice-data upload endpoints (`shipments()->uploadInvoiceData()`, `invoices()->uploadInvoiceData()`) require `function` and an `incoterm` on every declaration, and reject the fields only Create Shipment accepts: the invoice signature and summary fields, `exportReason`, `declarationNotes`, `packageMarks`, `payerVATNumber` and line-item `additionalInformation`.

#### DHL warnings the library does not enforce

Some combinations are accepted by DHL with a **warning** rather than an error, so the library sends them unchanged. Avoid them if the effect below is not what you want:

| DHL code | When | Effect |
|---|---|---|
| 7981 | `create(..., bypassLabelfreeError: true)` without the `PZ` (labelfree) value-added service | The flag is ignored |
| 7978 | Export declarations on a shipment with the `PM` value-added service | The declarations are ignored |

The library does reject, before sending, the combinations DHL always refuses: `bypassLabelfreeError` together with `bypassPLTError` (7183), duplicate invoice numbers across declarations (7188), and a content description made only of whitespace and `|` characters (7191).

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

// Cancel later. DHL answers 200 whether the pickup was cancelled, was already
// cancelled or had already been completed; the outcome text is in `message`.
// $result = $client->pickups()->cancel($confirmationNumber, 'Jan Novak', 'Order cancelled');
// echo $result->message; // e.g. "Successfully cancelled.
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

Timeout configuration is handled by your PSR-18 HTTP client, not by `ClientConfig`. When using the default Guzzle client, pass a configured instance via the `DhlClient` constructor:

```php
$client = new DhlClient(
    config: $config,
    httpClient: new \GuzzleHttp\Client(['timeout' => 30.0]),
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

### DHL Reference Sources

Enums, DTO shapes and error mappings are cross-referenced against the DHL documents in `specs/dhl/`:

1. `ExpressReferenceData_2026-10-25.xlsx` — reference data workbook, authoritative for code lists
2. `dpdhl-express-api-3.4.0_9.yaml` — MyDHL API 3.4.0 OpenAPI spec, authoritative for request/response shapes
3. `MyDHL API Reference data guide (3.4.0).pdf` — narrative reference guide

`dhl_reference_data.xlsx`, `dhl_openapi.yaml` and `dhl_reference.pdf` are historical 3.2.2-era copies kept for reference.

After DHL publishes a new workbook, `make shell` then `php tools/check-enums-against-workbook.php <path-to-xlsx>` lists every enum case missing from or extra to its workbook sheet.

### Integration Tests

Integration tests hit the real DHL Express sandbox. To run them locally:

```bash
export DHL_API_KEY=your_api_key
export DHL_API_SECRET=your_api_secret
export DHL_ACCOUNT_NUMBER=your_account_number

make test-integration
```

In GitHub Actions, the nightly `Integration Tests` workflow needs the same three values configured under repo settings — but split between **Secrets** and **Variables**:

- **Secrets** (Settings → Secrets and variables → Actions → Secrets): `DHL_API_KEY`, `DHL_API_SECRET`
- **Variables** (Settings → Secrets and variables → Actions → Variables): `DHL_ACCOUNT_NUMBER`

The account number is stored as a variable rather than a secret because workflow `if:` conditions can read variables but not secrets — that lets the workflow skip cleanly on forks where credentials aren't configured.

---

## License

MIT — see [LICENSE](LICENSE).

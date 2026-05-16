# Changelog

All notable changes to this project will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [0.3.0] — 2026-05-16

### Highlights

Major spec-alignment pass: every code-touching DHL API domain
(`/rates`, `/products`, `/tracking`, `/shipments`, `/pickups`,
`/invoices`, `/servicepoints`, `/early-shipment-screening`, `/epod`,
get-image) was reviewed against OpenAPI 3.2.2 and brought into
compliance. This release contains many breaking changes — see below
for the full migration list. After 0.3.0, all request and response
DTOs match the DHL wire contract verbatim; any field on the spec is
either modeled or explicitly deferred.

### Added

#### New API surface

- `TrackingApi::getManyByPieceId()` — per-piece tracking lookup (`GET /tracking` with `pieceTrackingNumber`).
- `TrackingApi::getManyByReference()` — reference-based tracking lookup (`GET /tracking` with `shipmentReference`).
- `TrackingApi::getByTrackingNumber()` gains optional `trackingView`, `levelOfDetail`, `requestControlledAccessDataCodes`, `requestGMTOffsetPerEvent` params.
- `TrackingApi::getMany()` gains optional `trackingView`, `levelOfDetail`, `requestControlledAccessDataCodes` params.
- `ProductsApi::list()` gains optional `requestEstimatedDeliveryDate` + `estimatedDeliveryDateType` params.
- `RatesApi::quoteMany()` gains optional `strictValidation` query param.
- `ShipmentApi::create()` now accepts `strictValidation` and `bypassPLTError` query-parameter flags.
- `EarlyShipmentScreeningApi` — `POST /early-shipment-screening` for BBX baby-shipment Denied Party screening.
- `InvoiceApi::uploadInvoiceData()` — standalone `POST /invoices/upload-invoice-data`.
- Round-trip integration test: create shipment → schedule pickup against the resulting tracking number → cancel pickup.

#### New request DTOs

- `CreateShipmentRequest`: `customerReferences`, `identifiers`, `documentImages`, `onDemandDelivery`, `requestOndemandDeliveryURL`, `shipmentNotification`, `prepaidCharges`, `getTransliteratedResponse`, `estimatedDeliveryDate`, `getAdditionalInformation`, `parentShipment`.
- `CustomerDetails`: 8 optional party slots — buyer, importer, exporter, seller, payer, manufacturer, ultimateConsignee, broker (new `ShipmentParty` wrapper carrying `registrationNumbers`, `bankDetails`, `typeCode`).
- `Content`: `areMorePackagesToBeAddedLater`, `USFilingTypeValue`.
- `OutputImageProperties`: `customerBarcodes`, `customerLogos`, `splitTransportAndWaybillDocLabels`, `allDocumentsInOneImage`, `splitDocumentsByPages`, `splitInvoiceAndReceipt`, `receiptAndLabelsInOneImage`.
- `ImageOption`: `invoiceType`, `languageCode`, `languageCountryCode`, `languageScriptCode`, `labelFreeText`, `labelCustomerDataText`, `shipmentReceiptCustomerDataText`.
- `Package` / `AddPiecePackage`: `identifiers`, `labelBarcodes`, `labelText`; `AddPiecePackage` also gains `labelDescription`, `referenceNumber`, `isThisTheLastPackageAdded`.
- `AddPieceOutputImageProperties`: `customerBarcodes`, `customerLogos`.
- `Pickup` (create-shipment sub-block): `closeTime`, `location`, `specialInstructions`, `pickupDetails`, `pickupRequestorDetails`.
- `ServicePointFindCriteria` + `ServicePointFindCriteriaBuilder` — exposes the full 30+ query-parameter surface of `GET /servicepoints`.
- `PickupValueAddedService` — pickup-side VAS (with `localServiceCode`, no `dangerousGoods`).

#### New value objects + enums

- `YearMonth` value object.
- Enums: `GetImageDocumentTypeCode`, `GetImageEncodingFormat`, `DocumentFunction`, `ValueAddedServiceMethod`, `EstimatedDeliveryDateTypeCode` (used by `/rates` and `/products` query params; distinct from `EstimatedDeliveryDateType` below, which models the create-shipment request body), `TrackingView`, `TrackingLevelOfDetail`, `InvoicePartyTypeCode`, `WeightUom`, `DimensionsUom`, `ResultUom`, `ServicePointCapability`, `ServicePointStatus`, `ServicePointOpenDay`, `YesNoIndicator`, `TrueFalseFlag`, `ServicePointTypeFilter`, `BarcodeSymbology`, `CustomerLogoFileFormat`, `InvoiceImageType`, `IdentifierTypeCode`, `LabelBarcodePosition`, `LabelTextPosition`, `OnDemandDeliveryOption`, `OnDemandWhereToLeave`, `EstimatedDeliveryDateType`, `AdditionalInformationType`.

### Changed

- **Breaking:** `ShipmentApi` get-image methods now accept `TrackingNumber` value objects instead of raw strings; the get-image request/response DTOs are now strongly typed. Migration: wrap any raw string in `new TrackingNumber(...)`.
- **Breaking:** `/rates` endpoints realigned to OpenAPI 3.2.2:
  - `RatesApi::quote()` — `originCityName` and `destinationCityName` promoted from optional to **required positional** params.
  - `RatesApi::quote()` — `?string $estimatedDeliveryDateType` is now `?EstimatedDeliveryDateTypeCode`.
  - `ValueAddedServiceFilter::__construct()` — `$dgContent` parameter replaced by `$method` (`ValueAddedServiceMethod`).
- **Breaking:** `/products` and `/tracking` realigned to OpenAPI 3.2.2:
  - `ProductsApi::list()` — `originCityName` and `destinationCityName` promoted from optional to **required positional** params.
  - `TrackingApi::getMany()` — first argument changed from variadic `TrackingNumber ...` to `array $trackingNumbers`.
- **Breaking:** `/invoices` and `/servicepoints` realigned; `ServicePointApi::find()` refactored from a 30-line named-argument signature to `ServicePointFindCriteria` + `ServicePointFindCriteriaBuilder`.
- **Breaking:** `dangerousGoods` no longer accepted at the `CreateShipmentRequest` root — it nests inside the matching `valueAddedServices[]` item per spec (`additionalProperties: false`). `CreateShipmentBuilder::withDangerousGoods()` is unchanged; it auto-attaches to the first DG-coded VAS.
- **Breaking:** `Content::__construct()` requires `Incoterm` (was optional; spec marks it required).
- **Breaking:** `OutputImageProperties::__construct()` no longer accepts `$renderDHLLogo` or `$fitLabelsToA4` — per spec they belong on each `ImageOption`.
- **Breaking:** `EpodApi::get()` — `$shipperAccountNumber` is now nullable (spec marks it optional). Existing positional callers continue to work.
- **Breaking:** `PickupShipmentDetails::$valueAddedServices` no longer accepts `Dto\Shipment\ValueAddedService`; use the new `Dto\Pickup\PickupValueAddedService` (carries `localServiceCode`, omits `dangerousGoods`).
- **Breaking:** `CreateShipmentBuilder::withPickupRequested(bool)` is replaced by `CreateShipmentBuilder::withPickup(Pickup)` so callers can supply the full sub-block.

### Removed

- `ValueAddedServiceFilter::$dgContent` constructor parameter (replaced by `$method`).
- `dangerousGoods` constructor parameter on `CreateShipmentRequest` (moved into `ValueAddedService`).
- `renderDHLLogo` / `fitLabelsToA4` constructor parameters on `OutputImageProperties` (moved to `ImageOption`).
- `CreateShipmentBuilder::withPickupRequested()` (replaced by `withPickup()`).

---

## [0.2.1] — 2026-05-15

### Fixed

- `GetImageRequest` now sends the `typeCode` query parameter in the spec-correct singular form (previously plural).

---

## [0.2.0] — 2026-05-09

### Changed

- **Breaking:** `guzzlehttp/guzzle` and `guzzlehttp/psr7` are no longer hard dependencies. The library now uses `php-http/discovery` to auto-detect a PSR-18 HTTP client and PSR-17 factories at runtime. Users who relied on Guzzle being pulled in transitively must now require it explicitly: `composer require medzuch/dhl-express-php guzzlehttp/guzzle`. Any other PSR-18 compliant client (e.g. `symfony/http-client`) works as a drop-in alternative.
- **Breaking:** `ClientConfig::$timeout` removed. Timeout is an HTTP-client concern — configure it on your PSR-18 client directly (e.g. `new GuzzleHttp\Client(['timeout' => 30.0])`) and inject it via the `DhlClient` constructor.

---

## [0.1.0] — 2026-05-09

Initial release of `medzuch/dhl-express-php`.

### Added

#### Core infrastructure
- `DhlClient` facade — single entry point delegating to domain API classes
- `ClientConfig` with sandbox/production environment toggle and configurable timeout
- PSR-18 HTTP transport with PSR-17 request/response factories; Guzzle 7 ships as the default
- PSR-3 logger integration — request/response payloads logged at `debug` level with `Authorization` header redacted
- Custom exception hierarchy: `DhlException` → `DhlApiException` → `DhlValidationException`, `DhlNetworkException`, `InvalidRequestException`
- `DhlErrorMapper` translating HTTP status codes and DHL error codes to typed exceptions
- `Support\HydrationHelper` — shared static helpers for defensive JSON-to-typed-PHP parsing across all API hydrators
- `DhlRateLimitException::$retryAfter` carries the integer-seconds value of the response `Retry-After` header
- `AccountNumber::__debugInfo()` masks the value when dumped, keeping only the last four digits

### Changed

- `TrackingApi::getByTrackingNumber()` now throws `DhlNotFoundException` when DHL returns no shipments. Previously it silently returned a `TrackingResponse` with empty-string fields, which masked the not-found case as a valid response.

#### Value Objects (self-validating, immutable)
- `TrackingNumber`, `Weight`, `Dimensions`, `Money`, `CountryCode`, `CurrencyCode`
- `PostalCode`, `EmailAddress`, `PhoneNumber`, `HsCode`
- `AccountNumber`, `MessageReference`

#### Enums (PHP 8.1+ backed strings)
- `WeightUnit`, `DimensionUnit`, `UnitSystem`
- `Incoterm`, `AccountTypeCode`, `PackageTypeCode`, `PickupLocationType`
- `ApiEnvironment`, `LabelEncodingFormat`, `LineItemQuantityUnit`
- `ProductCode` (~161 codes), `TrackingEventCode` (~140 codes), `LanguageCode`
- `ServiceCode` (383 codes), `OutputImageTemplate` (35 templates)
- `CommodityCategory` (108 codes), `ShipmentReferenceTypeCode`
- `DangerousGoodsServiceCode`

#### Tracking API (`GET /shipments/{id}/tracking`, `GET /tracking`)
- `TrackingApi::getByTrackingNumber()` — single shipment
- `TrackingApi::getMany()` — up to 200 shipments in one call

#### Identifier API (`GET /identifiers`)
- `IdentifierApi::allocate()` — resolve tracking/waybill identifiers

#### Address API (`POST /address-validate`)
- `AddressApi::validate()` — validate destination addresses

#### Products API (`GET /products`)
- `ProductsApi::list()` — list available shipping products for a lane

#### Reference Data API (`GET /reference-data`)
- `ReferenceDataApi::lookup()` — look up DHL code lists

#### EPOD API (`GET /shipments/{id}/proof-of-delivery`)
- `EpodApi::get()` — retrieve electronic proof of delivery

#### Service Points API (`GET /servicepoints`)
- `ServicePointApi::find()` — find DHL service points near an address

#### Rates API (`GET /rates`, `POST /rates`)
- `RatesApi::quote()` — single-piece rate request via GET
- `RatesApi::quoteMany()` — multi-piece rate request via POST
- `RateRequestBuilder` with cross-field validation (unit consistency, required fields)

#### Landed Cost API (`POST /landed-cost`)
- `LandedCostApi::estimate()` — estimate duties and taxes

#### Shipment API (`POST /shipments` and derivatives)
- `ShipmentApi::create()` — create a shipment with optional `validateDataOnly` mode
- `ShipmentApi::uploadImage()` — upload Paperless Trade (PLT) document image
- `ShipmentApi::uploadInvoiceData()` — upload structured customs invoice data
- `ShipmentApi::getImage()` — retrieve uploaded document images
- `ShipmentApi::addPiece()` — add pieces to an existing shipment
- `CreateShipmentBuilder` with cross-field validation:
  - Required-field presence (shipper, receiver, accounts, packages, etc.)
  - Account count: 1–3; package count: 1–999
  - Per-package weight/dimension unit alignment with shipment-level `unitOfMeasurement`
  - `isCustomsDeclarable=true` requires `exportDeclaration`
  - DG VAS code present requires `dangerousGoods` block
  - Insurance VAS (`II`) requires `declaredValue`
  - DDP incoterm requires a `DutiesTaxes` account
  - Cross-border outside EU customs territory requires `isCustomsDeclarable=true`
    (EU territory list includes 27 member states, Monaco, French outermost regions;
    Northern Ireland detected via `BT` postal-code prefix)
  - Line-item price×quantity sum must equal `declaredValue` within ±0.01

#### Pickup API (`POST /pickups`, `PATCH /pickups/{id}`, `DELETE /pickups/{id}`)
- `PickupApi::create()` — book a courier pickup
- `PickupApi::update()` — update an existing pickup booking
- `PickupApi::cancel()` — cancel a pickup booking
- `PickupRequestBuilder` with cross-field validation:
  - Required fields (shipper, datetime, accounts, shipment details)
  - Account count: 1–5
  - Shipment details count: 1–999
  - `plannedPickupDateAndTime` must be in the future and at most 10 days ahead
  - `closeTime` must be strictly after the pickup time when both are provided

[0.1.0]: https://github.com/medzuch/dhl-express-php/releases/tag/v0.1.0

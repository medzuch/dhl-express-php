# Changelog

All notable changes to this project will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [0.4.0] — Unreleased

### Highlights

Hard switch to **MyDHL API 3.4.0**. The library now sends `x-version: 3.4.0`, and every Create Shipment and invoice-data payload follows the 3.4.0 schemas (built payloads are validated against `specs/dhl/dpdhl-express-api-3.4.0_9.yaml` in the test suite). Code enums are regenerated from DHL's 2026-10-25 reference data workbook (`ExpressReferenceData_2026-10-25.xlsx`), which removes codes the live API now rejects (error 7021). The 3.2.2-era spec files stay in `specs/dhl/` as historical references. This release contains breaking changes; see **Migrating from 0.3.x**.

### Added

- `CreateShipmentBuilder::addExportDeclaration()`: a shipment carries 1–100 export declarations, serialized as `content.exportDeclarations[]`.
- `ExportDeclaration`: `incoterm`, `exportReason` (≤ 30 chars), `declarationNotes` (new `DeclarationNote` DTO, ≤ 3), `packageMarks`, `payerVATNumber`, and the `withIncoterm()` copy method.
- `ExportInvoice`: `signatureName`, `signatureTitle`, `signatureImage`, `instructions`, `customerDataTextEntries`, `totalNetWeight`, `totalGrossWeight`, `termsOfPayment`, plus `toInvoiceDataArray()` and `shipmentOnlyFields()`.
- `ExportLineItem::$additionalInformation` (≤ 8 entries).
- `ExportDeclaration::toInvoiceDataArray()` and `invoiceDataUploadErrors()`.
- `Content::$dimensionUnitOfMeasurement` and `CreateShipmentBuilder::withDimensionUnitSystem()`: dimensions may use a different unit system from weights.
- `DangerousGoods`: `packagesCount` (1–999) and `dryIceUnitOfMeasurement`; `customDescription` is limited to the schema maximum of 1000 characters.
- `ShipmentApi::create()` gains `?bool $bypassLabelfreeError`.
- `ShipmentDocument::$invoiceNumber`, hydrated from the Create Shipment response.
- `CancelPickupResponse` DTO and `CancelPickupResponseHydrator`.
- `TrackingApi::getMany()` and `getManyByReference()` gain `requestGMTOffsetPerEvent`; `getManyByPieceId()` gains the full set of view options.
- Enum cases from the 2026-10-25 workbook:
  - `ServiceCode`: 84 new codes, including `YL` (temperature-controlled `1T`, `5A`–`5Z`, `6A`–`6Y`, `LK`–`LP`, `MC`–`MS`; GoGreen Plus `F1`/`FQ`; `CU`, `CV`, `GI`, `P3`, `W8`, `W9`, `YA`, `YL`).
  - `DangerousGoodsContentId::A08` and `DangerousGoodsServiceCode::H8` (ADR Special Provision 188/400).
  - `RegistrationNumberTypeCode::FSR` (Food Safety Registration).
  - `InvoiceReferenceTypeCode::AKO`.
  - `LineItemReferenceTypeCode::AKO`, `MF`, `SRV`.
- Pre-send checks for errors DHL is certain to return:
  - 7183: `bypassLabelfreeError` and `bypassPLTError` both true (`InvalidArgumentException`).
  - 7188: duplicate invoice numbers across export declarations (`InvalidRequestException`).
  - 7191: a content description made only of whitespace and `|` characters (`InvalidRequestException`).
- `tools/check-enums-against-workbook.php`: a dev-only drift check between the code enums and a reference data workbook (not shipped in the package).

### Changed

- **BREAKING** The default `ClientConfig::$xVersion` is `3.4.0` (was `3.2.0`).
- **BREAKING** `Content::$exportDeclaration` (`?ExportDeclaration`) is replaced by `Content::$exportDeclarations` (`list<ExportDeclaration>`).
- **BREAKING** `ExportDeclaration::$invoice` is required.
- **BREAKING** The invoice-data upload requests (`UploadInvoiceDataRequest`, `UploadStandaloneInvoiceDataRequest`) require an `incoterm` and an `invoice.function` on every declaration. They reject the Create-Shipment-only fields listed under **Added**.
- **BREAKING** `PickupApi::cancel()` returns `CancelPickupResponse` (was `void`).
- **BREAKING** `TrackingApi::getManyByPieceId()` takes `list<string>` instead of variadic strings.
- `Content::$incoterm` and the builder's shipment-level incoterm are optional. When set, the shipment-level incoterm is the default for declarations without their own; a declarable shipment needs an incoterm on every declaration, inherited or explicit.
- `ExportInvoice::$function` is optional.
- `LineItemWeight` requires `netValue` or `grossValue`, as 3.4.0 does (already enforced by the constructor in 0.3.x).
- The DDP duties/taxes-account rule considers the shipment-level incoterm and every declaration's incoterm. The declared-value check sums line items across all declarations.
- CLAUDE.md and README cite the 3.4.0 OpenAPI spec, the 2026-10-25 workbook and the 3.4.0 reference data guide, and enum docblocks cite the new workbook.

### Fixed

- Create Shipment no longer sends `invoice.function`, which the Create Shipment schema rejects (3.2.2 and 3.4.0).
- The invoice-data upload endpoints now send the `incoterm` that their schema requires on every declaration.

### Removed

- **BREAKING** `CreateShipmentBuilder::withExportDeclaration()`; use `addExportDeclaration()`.
- **BREAKING** `ParentShipment::$typeCode`, `$number` and `$issuerCountryCode` (3.4.0 removed them).
- **BREAKING** `OnDemandDelivery::$requestedDeliveryDate` (3.4.0 removed it).
- **BREAKING** Enum cases retired by DHL in the 2026-10-25 workbook:
  - `ShipmentReferenceTypeCode` (47): `AAM`, `AAO`, `AAP`, `ABT`, `ACR`, `ACS`, `ADA`, `AFD`, `AHX`, `ANT`, `BKN`, `BOE`, `BOL`, `CDN`, `CO`, `COD`, `CR`, `CRN`, `CTN`, `DSC`, `FF`, `FN`, `GRS`, `HWB`, `IBC`, `LLR`, `MAB`, `MR1`, `MR2`, `MR3`, `MR4`, `MR5`, `MR6`, `MWB`, `NET`, `OBC`, `PBE`, `PD`, `PRN`, `RTL`, `SID`, `SP`, `SRN`, `SS`, `STD`, `SWN`, `UCI`.
  - `PackageReferenceTypeCode` (41): `AAM`, `AAO`, `AAP`, `ABT`, `ACR`, `ACS`, `ADA`, `AFD`, `AHX`, `ANT`, `BKN`, `BOL`, `CDN`, `CO`, `COD`, `CR`, `CRN`, `CTN`, `DSC`, `FF`, `FN`, `GRS`, `HWB`, `IBC`, `IME`, `IPP`, `LLR`, `MAB`, `MWB`, `NET`, `OBC`, `PD`, `PRN`, `RTL`, `SID`, `SP`, `SRN`, `SS`, `STD`, `SWN`, `UCI`.
  - `PackageTypeCode`: `WB1`, `WB2`, `WB3`, `WB6`.
  - `CustomsDocumentTypeCode`: `CHA`, `POA`.
  - `ServiceCode`: `AmbientShipperBoxS` (`U7`), `AmbientShipperBoxM` (`U8`), `AmbientShipperBoxL` (`U9`).

### Migrating from 0.3.x

- **x-version:** requests now carry `x-version: 3.4.0`. To keep sending 3.2.0 headers, pass `xVersion: '3.2.0'` to `ClientConfig`; payloads are 3.4.0-shaped either way.
- **Export declarations:** replace `->withExportDeclaration($declaration)` with `->addExportDeclaration($declaration)`, and call it once per declaration. Read `$content->exportDeclarations` (a list) instead of `$content->exportDeclaration`.
- **Invoice on declarations:** every `new ExportDeclaration(...)` needs `invoice: new ExportInvoice(...)`.
- **Incoterm on declarations:** for Create Shipment, either keep `->withIncoterm(...)` on the builder (declarations inherit it) or pass `incoterm:` to each `ExportDeclaration`. For invoice-data uploads, pass `incoterm:` to each declaration and keep `function:` on each `ExportInvoice`.
- **Invoice-data uploads:** don't set the Create-Shipment-only fields on declarations you upload; the request constructors now reject them with `InvalidArgumentException`.
- **`LineItemWeight`:** a weight needs `netValue`, `grossValue` or both. The constructor already enforced this in 0.3.x, so code that runs on 0.3.x needs no change; 3.4.0 now also requires it on the wire.
- **`ParentShipment`:** pass only `productCode` and `packagesCount`.
- **`OnDemandDelivery`:** drop the `requestedDeliveryDate` argument.
- **`PickupApi::cancel()`:** the return value is now a `CancelPickupResponse`. Existing calls that ignore it keep working; read `$result->message` for DHL's outcome text.
- **`TrackingApi::getManyByPieceId()`:** change `getManyByPieceId('a', 'b')` to `getManyByPieceId(['a', 'b'])`.
- **Removed enum cases:** replace any case listed under **Removed** with a current code. DHL rejects the retired codes with error 7021, and PHPStan reports the references. `PackageTypeCode::CardEnvelopeImperial` (`CE1`) and `PackageReferenceTypeCode::AAJ` are kept, because two of DHL's three sources still list them.
- **New pre-send checks:** builds that duplicate an invoice number across declarations, or use a description such as `" || "`, now fail in `build()` (7188, 7191). Calling `create()` with both bypass flags set to `true` throws before sending (7183).

---

## [0.3.2] — 2026-05-16

### Changed

- Converted 15 service classes from `final class` to `final readonly class`: all 13 `*Api` classes (`AddressApi`, `EarlyShipmentScreeningApi`, `EpodApi`, `IdentifierApi`, `InvoiceApi`, `LandedCostApi`, `PickupApi`, `ProductsApi`, `RatesApi`, `ReferenceDataApi`, `ServicePointApi`, `ShipmentApi`, `TrackingApi`), plus `RequestBuilder` and `ResponseParser`. The class-level `readonly` modifier makes immutability explicit and enforced by PHP; the now-redundant `readonly` keyword has been removed from all constructor-promoted properties in those classes.

### Fixed

- `PickupRequestBuilderTest`: use a fixed `08:00 UTC` time in `minimalBuilder()` instead of the current wall-clock time, preventing flaky failures when the test suite runs after 18:00.

---

## [0.3.1] — 2026-05-16

### Fixed

- README: replace removed `withPickupRequested(bool)` with `withPickup(new Pickup(isRequested: false))` in both builder examples; add missing `Pickup` import.
- README: add Early Shipment Screening to the Features table (was added in v0.3.0 but omitted).

### Changed

- Renamed `docs/dhl/` → `specs/dhl/` — the folder now only holds vendor API specifications; the new name makes its purpose explicit.
- Removed internal session-scoped planning documents (`PROJECT_PLAN.md`, `docs/DECISIONS.md`, `docs/PROJECT_HISTORY.md`, `docs/ROADMAP.md`) and a stale DHL announcement email that were accidentally shipped in the repo.
- Scrubbed "Phase N" labels from class/method docblocks and test section comments; renamed `CreateShipmentBuilderPhase4bTest` → `CreateShipmentBuilderCustomsValidationTest`.

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
- Enums: `GetImageDocumentTypeCode`, `GetImageEncodingFormat`, `DocumentFunction`, `ValueAddedServiceMethod`, `TrackingView`, `TrackingLevelOfDetail`, `InvoicePartyTypeCode`, `WeightUom`, `DimensionsUom`, `ResultUom`, `ServicePointCapability`, `ServicePointStatus`, `ServicePointOpenDay`, `YesNoIndicator`, `TrueFalseFlag`, `ServicePointTypeFilter`, `BarcodeSymbology`, `CustomerLogoFileFormat`, `InvoiceImageType`, `IdentifierTypeCode`, `LabelBarcodePosition`, `LabelTextPosition`, `OnDemandDeliveryOption`, `OnDemandWhereToLeave`, `EstimatedDeliveryDateType`, `AdditionalInformationType`.

### Changed

- **Breaking:** `ShipmentApi` get-image methods now accept `TrackingNumber` value objects instead of raw strings; the get-image request/response DTOs are now strongly typed. Migration: wrap any raw string in `new TrackingNumber(...)`.
- **Breaking:** `/rates` endpoints realigned to OpenAPI 3.2.2:
  - `RatesApi::quote()` — `originCityName` and `destinationCityName` promoted from optional to **required positional** params.
  - `RatesApi::quote()` — `?string $estimatedDeliveryDateType` is now `?EstimatedDeliveryDateTypeCode` (the existing enum from v0.1.0; `ProductsApi::list()`'s new `estimatedDeliveryDateType` param also uses it). Distinct from the new `EstimatedDeliveryDateType` enum that models the create-shipment request body — same QDDC/QDDF values, separate usage contexts.
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

[0.3.1]: https://github.com/medzuch/dhl-express-php/releases/tag/v0.3.1
[0.3.0]: https://github.com/medzuch/dhl-express-php/releases/tag/v0.3.0
[0.2.1]: https://github.com/medzuch/dhl-express-php/releases/tag/v0.2.1
[0.2.0]: https://github.com/medzuch/dhl-express-php/releases/tag/v0.2.0
[0.1.0]: https://github.com/medzuch/dhl-express-php/releases/tag/v0.1.0

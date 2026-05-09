# Changelog

All notable changes to this project will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- `TrackingApi::track()` — single shipment
- `TrackingApi::trackMultiple()` — up to 10 shipments in one call

#### Identifier API (`GET /identifiers`)
- `IdentifierApi::resolve()` — resolve tracking/waybill identifiers

#### Address API (`POST /address-validate`)
- `AddressApi::validate()` — validate destination addresses

#### Products API (`GET /products`)
- `ProductsApi::getProducts()` — list available shipping products for a lane

#### Reference Data API (`GET /reference-data`)
- `ReferenceDataApi::getData()` — look up DHL code lists

#### EPOD API (`GET /shipments/{id}/proof-of-delivery`)
- `EpodApi::get()` — retrieve electronic proof of delivery

#### Service Points API (`GET /servicepoints`)
- `ServicePointApi::find()` — find DHL service points near an address

#### Rates API (`GET /rates`, `POST /rates`)
- `RatesApi::getRates()` — single-piece rate request via GET
- `RatesApi::postRates()` — multi-piece rate request via POST
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

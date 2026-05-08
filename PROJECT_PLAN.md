# DHL Express PHP Library — Project Plan

> **Memory document for Claude sessions.** This file captures all decisions, conventions, architecture, and implementation roadmap for the `medzuch/dhl-express-php` library. Refer to this in future Claude sessions to maintain context.

---

## 1. Project Identity

| Field | Value |
|---|---|
| Package name | `medzuch/dhl-express-php` |
| Type | Standalone PHP library (framework-agnostic) |
| Namespace | `Medzuch\DhlExpress` |
| License | MIT |
| Repository | `git@github.com:medzuch/dhl-express-php.git` |

---

## 2. Tech Stack & Versions

| Component | Version | Notes |
|---|---|---|
| PHP | 8.3 | `^8.3` constraint in composer.json |
| Composer | latest | Inside container |
| HTTP client | PSR-18 + Guzzle 7.9 | `psr/http-client` + `psr/http-factory` interfaces; Guzzle as default implementation |
| Logging | PSR-3 (`psr/log` ^3.0) | Optional `LoggerInterface` injected through `DhlClient` → `HttpTransport`; `NullLogger` default |
| Testing | PHPUnit 11 | Unit + Integration suites |
| Static analysis | PHPStan 2.x (^2.1, level 8) | Strictest level; separate config for tests at level 6 |
| Code style | php-cs-fixer 3.x | PSR-12 + PHP 8.3 migration rules |
| HTTP mocking | `php-http/mock-client` + `nyholm/psr7` | For unit tests |

### Daily Workflow
```bash
make up             # start containers
make install        # composer install
make test               # phpunit unit suite only (integration excluded by default)
make test-integration   # phpunit integration suite (requires DHL_API_KEY)
make analyse            # phpstan analyse src/ (level 8)
make analyse-tests      # phpstan analyse tests/ (level 6)
make analyse-all        # phpstan analyse src/ + tests/
make check              # test + analyse-all + cs-check
make shell          # enter container
make down           # stop
```

---

## 3. DHL Express API Overview

**API version:** MyDHL API 3.2.2 (release date 5 Apr 2026)
**Spec:** OpenAPI 3.0.0
**Authentication:** HTTP Basic Auth (username + password)
**Content type:** `application/json`
**Reference docs (precedence — primary first):**
1. `docs/dhl/dhl_reference_data.xlsx` — machine-readable workbook from DHL's Reference Data API (19 sheets: countries, productCode, serviceCode, packageTypeCode, documentTypeCode, registrationNumberTypeCode, trackingEventCode, returnStatusMessage, etc.). **Authoritative source for value lists.**
2. `docs/dhl/dhl_openapi.yaml` — authoritative for request/response shapes and field constraints.
3. `docs/dhl/dhl_reference.pdf` — narrative reference; secondary because it contains legacy codes the live API no longer accepts.

### Environments
- **Sandbox/Test:** `https://express.api.dhl.com/mydhlapi/test`
- **Production:** `https://express.api.dhl.com/mydhlapi`

### Common HTTP Headers
- `Message-Reference` (1-36 chars, UUID-style)
- `Message-Reference-Date` (RFC 1123 datetime)
- `Plugin-Name` / `Plugin-Version` (3PV identification)
- `ShippingSystemPlatform-Name` / `ShippingSystemPlatform-Version`
- `WebstorePlatform-Name` / `WebstorePlatform-Version`
- `x-version` (API version, e.g. `2.12.0`)
- `Accept-Language` (3-char language code, default `eng`)

### API Endpoints (21 operations across 11 domains)

| Domain | Endpoints | Purpose |
|---|---|---|
| **Pickup** | `GET/POST /pickups`, `PATCH/DELETE /pickups/{id}` | Schedule, update, cancel pickups |
| **Identifier** | `GET /identifiers` | Resolve tracking IDs |
| **Address** | `POST /address-validate` | Validate destination addresses |
| **Rates** | `GET /rates`, `POST /rates` | Get shipping rate quotes (single-piece GET, multi-piece POST) |
| **Landed Cost** | `POST /landed-cost` | Estimate duties/taxes |
| **Products** | `GET /products` | List available shipping products |
| **Tracking** | `GET /shipments/{id}/tracking`, `GET /tracking` | Track shipments (single + multi) |
| **EPOD** | `GET /shipments/{id}/proof-of-delivery` | Electronic proof of delivery |
| **Shipment** | `POST /shipments`, image upload, invoice data, get-image, add-piece | Create and manage shipments |
| **Service Points** | `GET /servicepoints` | Find DHL service points |
| **Reference Data** | `GET /reference-data` | Lookup codes/values |
| **Early Screening** | `POST /early-shipment-screening` | Pre-shipment compliance check |

---

## 4. Design Principles & Guidelines

### Patterns to USE
1. **Facade (`DhlClient`)** — single entry point that delegates to domain APIs
2. **DTOs with PHP 8.3 readonly properties** — typed request/response objects, no raw arrays
3. **Value Objects** — for `TrackingNumber`, `Weight`, `Money`, `CountryCode`, `PostalCode`, `AccountNumber`
4. **Enums (PHP 8.1+ backed)** — for all DHL code lists (incoterms, package types, etc.)
5. **Builder pattern** — for complex requests (CreateShipmentRequest has deep nesting)
6. **Repository-style API classes** — one class per DHL domain
7. **Custom exception hierarchy** — never leak Guzzle exceptions to consumers
8. **PSR-18 HTTP client** — code against `Psr\Http\Client\ClientInterface` and `Psr\Http\Message\RequestFactoryInterface`; Guzzle ships as the default but users can inject any PSR-18 compliant client
9. **Ubiquitous Language** — mirror DHL's own terminology (Shipment, Piece, Waybill, ServicePoint, Pickup)

### Anti-patterns to AVOID
- ❌ God class — `DhlClient` delegates, never implements
- ❌ Raw arrays for API data
- ❌ Leaking Guzzle exceptions
- ❌ Hardcoded URLs/timeouts/credentials
- ❌ Static methods (untestable, can't mock)
- ❌ Leaking Guzzle types — always type-hint against PSR-18 interfaces, never concrete Guzzle classes
- ❌ Ignoring HTTP status codes

### Methodology
- **TDD strict** — write test first, then implementation
- **DDD selectively** — Value Objects + Ubiquitous Language YES; aggregates/repositories/events NO
- **PHPStan level 8** from day one — no `mixed` types unless absolutely necessary
- All public APIs must have full type declarations and docblocks
- Constructor property promotion + readonly everywhere DTOs are used

---

## 5. Folder Structure

```
dhl-express-php/
├── docs/
│   └── dhl/                                    # DHL official documentation
│       ├── dhl_openapi.yaml
│       └── dhl_reference.pdf
├── src/
│   ├── DhlClient.php                           # Main facade
│   ├── ClientConfig.php                        # Base URL, timeout, sandbox flag
│   ├── Auth/
│   │   └── Credentials.php                     # Basic auth value object
│   ├── Http/
│   │   ├── HttpClientInterface.php             # Thin wrapper — accepts Psr\Http\Client\ClientInterface
│   │   ├── GuzzleHttpClient.php                # Guzzle implementation
│   │   ├── RequestBuilder.php                  # Builds requests with required headers
│   │   ├── ResponseParser.php                  # Parses JSON + error responses
│   │   └── RetryStrategy.php                   # Optional retry logic for 5xx
│   ├── Api/                                    # One class per DHL domain
│   │   ├── PickupApi.php
│   │   ├── IdentifierApi.php
│   │   ├── AddressApi.php
│   │   ├── RatesApi.php
│   │   ├── LandedCostApi.php
│   │   ├── ProductsApi.php
│   │   ├── TrackingApi.php
│   │   ├── EpodApi.php                         # Electronic Proof of Delivery
│   │   ├── ShipmentApi.php
│   │   ├── ServicePointApi.php
│   │   ├── ReferenceDataApi.php
│   │   └── EarlyShipmentScreeningApi.php
│   ├── Builder/                                # Builders for complex requests
│   │   ├── CreateShipmentBuilder.php
│   │   ├── RateRequestBuilder.php
│   │   └── PickupRequestBuilder.php
│   ├── Dto/                                    # Data Transfer Objects (request/response)
│   │   ├── Common/
│   │   │   ├── Address.php
│   │   │   ├── Contact.php
│   │   │   ├── ContactAddress.php              # Address + Contact composite
│   │   │   ├── RegistrationNumber.php
│   │   │   └── BankDetails.php
│   │   ├── Shipment/
│   │   │   ├── CreateShipmentRequest.php
│   │   │   ├── CreateShipmentResponse.php
│   │   │   ├── Package.php                     # Piece dimensions/weight
│   │   │   ├── PackageReference.php
│   │   │   ├── ExportDeclaration.php
│   │   │   ├── Invoice.php
│   │   │   ├── LineItem.php
│   │   │   ├── ValueAddedService.php
│   │   │   ├── DocumentImage.php
│   │   │   ├── OutputImageProperties.php
│   │   │   ├── DangerousGoods.php
│   │   │   └── OnDemandDelivery.php
│   │   ├── Pickup/
│   │   │   ├── PickupRequest.php
│   │   │   ├── PickupResponse.php
│   │   │   ├── UpdatePickupRequest.php
│   │   │   └── UpdatePickupResponse.php
│   │   ├── Rate/
│   │   │   ├── RateRequest.php
│   │   │   ├── RateRequestMany.php
│   │   │   ├── RateResponse.php
│   │   │   └── RateAddress.php
│   │   ├── Tracking/
│   │   │   ├── TrackingResponse.php
│   │   │   ├── ShipmentEvent.php
│   │   │   └── PieceEvent.php
│   │   ├── Address/
│   │   │   ├── AddressValidateResponse.php
│   │   │   └── ValidatedAddress.php
│   │   ├── ServicePoint/
│   │   │   ├── ServicePoint.php
│   │   │   ├── ServicePointCapabilities.php
│   │   │   ├── OpeningHours.php
│   │   │   └── GeoLocation.php
│   │   ├── ReferenceData/
│   │   │   ├── ReferenceDataRequest.php
│   │   │   └── ReferenceDataResponse.php
│   │   ├── LandedCost/
│   │   │   ├── LandedCostRequest.php
│   │   │   └── LandedCostResponse.php
│   │   ├── Identifier/
│   │   │   └── IdentifierResponse.php
│   │   ├── Epod/
│   │   │   └── EpodResponse.php
│   │   ├── EarlyScreening/
│   │   │   ├── EarlyScreeningRequest.php
│   │   │   └── EarlyScreeningResponse.php
│   │   └── Image/
│   │       ├── ImageUploadRequest.php
│   │       ├── DocumentImageResponse.php
│   │       ├── UploadInvoiceDataRequest.php
│   │       └── UploadInvoiceDataResponse.php
│   ├── ValueObject/                            # Self-validating immutable values
│   │   ├── TrackingNumber.php
│   │   ├── AccountNumber.php
│   │   ├── CountryCode.php                     # ISO 3166-1 alpha-2
│   │   ├── CurrencyCode.php                    # ISO 4217
│   │   ├── PostalCode.php                      # Country-aware validation
│   │   ├── Weight.php                          # value + unit
│   │   ├── Dimensions.php                      # length × width × height + unit
│   │   ├── Money.php                           # amount + currency
│   │   ├── EmailAddress.php
│   │   ├── PhoneNumber.php
│   │   ├── ServiceAreaCode.php
│   │   ├── HsCode.php                          # Harmonized System code
│   │   └── MessageReference.php                # UUID-style request reference
│   ├── Enum/                                   # PHP 8.1+ backed enums (string-backed)
│   │   ├── ApiEnvironment.php                  # Sandbox, Production
│   │   ├── UnitSystem.php                      # metric, imperial (DHL wire format)
│   │   ├── WeightUnit.php                      # KG, LB (with ->system() helper)
│   │   ├── DimensionUnit.php                   # CM, IN (with ->system() helper)
│   │   ├── DistanceUnit.php                    # km, mi (Service Point distance)
│   │   ├── DayOfWeek.php                       # MONDAY..SUNDAY + HOLIDAY
│   │   ├── ServicePointType.php                # CITY, STATION, PARTNER, TWENTYFOURSEVEN
│   │   ├── LabelEncodingFormat.php             # pdf, zpl, lp2, epl
│   │   ├── Incoterm.php                        # EXW, FCA, CPT, CIP, DPU, DAP, DDP + 9 legacy
│   │   ├── PackageTypeCode.php                 # 22 DHL-supplied global package types (incl. BB1-BB6 bottle boxes)
│   │   ├── BusinessPartyTypeCode.php           # BU, DC, GV, OT, PR, RE
│   │   ├── OtherChargeTypeCode.php             # ADMIN, DELIV, DOCUM, ... (15 codes)
│   │   ├── LandedCostRateType.php              # default_rate, derived_rate, ... (6 codes)
│   │   ├── RegistrationNumberTypeCode.php      # VAT, EIN, EOR, CNP, ... (25 codes)
│   │   ├── PackageReferenceTypeCode.php        # CU, AAO, MRN, HWB, ... (81 codes)
│   │   ├── InvoiceReferenceTypeCode.php        # ACL, CID, CN, CU, ITN, MRN, ... (19 codes)
│   │   ├── LineItemReferenceTypeCode.php       # AFE, AAJ, ABW, ALX, ... (41 codes)
│   │   ├── CustomsDocumentTypeCode.php         # Single enum used at invoice + line-item level (55 codes incl. 972 → T2LFDispense)
│   │   ├── DangerousGoodsContentId.php         # 23 content classifications
│   │   ├── DangerousGoodsServiceCode.php       # 16 unique service codes (HY, HL, HN, HU, HA, HB, YN, …)
│   │   ├── ProductCode.php                     # 36 single-char DHL Express product codes (DOX, ECX, WPX, …)
│   │   ├── TrackingEventCode.php               # 65 two-letter tracking event codes (OK, PU, CR, RT, …)
│   │   ├── LanguageCode.php                    # 46 lowercase 3-letter language codes (eng default)
│   │   └── UnitOfMeasurement.php               # 59 customs line-item quantity units (DOZ, PCS, M3, …)
│   ├── Exception/                              # Custom exception hierarchy
│   │   ├── DhlException.php                    # Base exception (abstract)
│   │   ├── DhlNetworkException.php             # Connection/timeout failures
│   │   ├── DhlAuthenticationException.php      # 401
│   │   ├── DhlAuthorizationException.php       # 403
│   │   ├── DhlValidationException.php          # 400, 422
│   │   ├── DhlNotFoundException.php            # 404
│   │   ├── DhlRateLimitException.php           # 429
│   │   ├── DhlServerException.php              # 5xx
│   │   ├── DhlApiException.php                 # Business-level errors with DHL error code
│   │   └── ErrorCode/
│   │       ├── DhlErrorCode.php                # Enum of all error codes (999, 9001, 996, etc.)
│   │       └── DhlErrorMapper.php              # Maps DHL error codes → exceptions
│   └── Support/
│       ├── Json.php                            # Safe JSON encode/decode
│       ├── Validator.php                       # Internal validators
│       └── Hydrator.php                        # Array → DTO conversion
├── tests/
│   ├── Unit/                                   # Mocked HTTP, isolated tests
│   │   ├── ValueObject/                        # One test file per VO
│   │   ├── Enum/                               # Enum tests
│   │   ├── Dto/                                # DTO tests
│   │   ├── Builder/                            # Builder tests
│   │   ├── Api/                                # API class tests with mock HTTP
│   │   └── Http/                               # HTTP wrapper tests
│   ├── Integration/                            # Real DHL sandbox calls
│   │   ├── PickupIntegrationTest.php
│   │   ├── RatesIntegrationTest.php
│   │   ├── TrackingIntegrationTest.php
│   │   └── ShipmentIntegrationTest.php
│   ├── Fixtures/                               # JSON fixtures from DHL responses
│   │   ├── rates/
│   │   ├── shipments/
│   │   ├── tracking/
│   │   └── pickups/
│   └── TestCase.php                            # Base test class with helpers
├── examples/                                   # Usage examples (not part of library)
│   ├── 01-track-shipment.php
│   ├── 02-get-rates.php
│   ├── 03-create-shipment.php
│   └── 04-schedule-pickup.php
├── .env.example
├── .gitignore
├── .gitattributes
├── .php-cs-fixer.php
├── phpstan.neon
├── phpunit.xml
├── Makefile
├── Dockerfile
├── docker-compose.yml
├── composer.json
├── LICENSE
├── README.md
├── CHANGELOG.md
└── PROJECT_PLAN.md                             # This file
```

---

## 6. Key Type Design Decisions

### Value Objects (self-validating, immutable)
```php
final readonly class TrackingNumber {
    public function __construct(public string $value) {
        if (!preg_match('/^\d{10}$/', $value)) {
            throw new \InvalidArgumentException("Invalid DHL tracking number");
        }
    }
}

final readonly class Weight {
    public function __construct(
        public float $value,
        public WeightUnit $unit,
    ) {
        if ($value <= 0) throw new \InvalidArgumentException("Weight must be positive");
    }
    public function inKilograms(): float { /* conversion logic */ }
}

final readonly class Money {
    public function __construct(
        public float $amount,
        public CurrencyCode $currency,
    ) {}
}
```

### Enums (PHP 8.1+ backed string enums)
```php
enum Incoterm: string {
    case EXW = 'EXW';
    case FCA = 'FCA';
    case CPT = 'CPT';
    case CIP = 'CIP';
    case DAP = 'DAP';
    case DPU = 'DPU';
    case DDP = 'DDP';
    case FAS = 'FAS';
    case FOB = 'FOB';
    case CFR = 'CFR';
    case CIF = 'CIF';
    case DAF = 'DAF';
    case DAT = 'DAT';
    case DDU = 'DDU';
    case DEQ = 'DEQ';
    case DES = 'DES';

    public function description(): string { /* return human-readable */ }
}

enum UnitSystem: string {
    case Metric = 'metric';     // DHL wire format
    case Imperial = 'imperial';
}

enum WeightUnit: string {
    case KG = 'KG';
    case LB = 'LB';

    public function system(): UnitSystem {
        return match ($this) {
            self::KG => UnitSystem::Metric,
            self::LB => UnitSystem::Imperial,
        };
    }
}
```

### DTOs (readonly, named arguments)
```php
final readonly class CreateShipmentRequest {
    public function __construct(
        public \DateTimeImmutable $plannedShippingDate,
        public Incoterm $incoterm,
        public ProductCode $productCode,
        public ContactAddress $shipper,
        public ContactAddress $recipient,
        /** @var Package[] */
        public array $packages,
        public ?ExportDeclaration $exportDeclaration = null,
        /** @var ValueAddedService[] */
        public array $valueAddedServices = [],
        public ?OutputImageProperties $outputImageProperties = null,
        public bool $isCustomsDeclarable = false,
        public string $description = '',
    ) {}

    public function toArray(): array { /* serialize to DHL JSON shape */ }
}
```

---

## 7. Validation Strategy

Validation is layered. Each layer has a single responsibility, and we add complexity only when a real DHL constraint requires it.

### Layer 1 — Self-validating types (constructor enforcement)
- **Where:** Value Objects and DTOs validate in the constructor and throw `\InvalidArgumentException` on bad input.
- **Why:** Once an instance exists it is provably valid. Functions that accept `CountryCode` instead of `string` cannot receive malformed input — the type is the proof.
- **What gets validated here:**
    - VO format rules: `TrackingNumber` (non-empty), `Weight` (positive value), `Money` (currency consistency), `CountryCode` (ISO 3166-1 alpha-2), `CurrencyCode` (ISO 4217), `PostalCode`, `EmailAddress`, `PhoneNumber`, `HsCode`, `MessageReference` (1-36 chars).
    - DTO single-field length / range / enum constraints from `docs/dhl/dhl_openapi.yaml`.
- **No external validator framework.** PHP 8.3 type system + the constructor is the schema.

### Layer 2 — Builder-time cross-field rules
- **Where:** `CreateShipmentBuilder`, `RateRequestBuilder`, `PickupRequestBuilder`. The `build()` method runs cross-field checks after all setters have been called, then either returns a valid DTO or throws.
- **Why:** Some rules involve multiple fields and the OpenAPI spec under-specifies them. Catching these client-side avoids a network round-trip and gives the caller a clearer error than a generic DHL 400.
- **Error model:** errors are **accumulated** during `build()` and thrown together as a single `InvalidRequestException extends DhlException` carrying a list of `{field, message}` entries. (Decision: accumulate + throw once, not fail-fast — better DX when several fields are wrong.)
- **Examples of rules that live here:**
    - `isCustomsDeclarable=true` ⇒ `exportDeclaration` required.
    - Shipper country ≠ recipient country ⇒ customs data needed (with EU intra-zone exemption — list pulled from `docs/dhl/dhl_reference.pdf`).
    - Dangerous-goods VAS code present ⇒ `DangerousGoods` block required.
    - Insurance VAS (II) ⇒ declared `Money` value required.
    - DDP incoterm ⇒ payer details required.
    - All packages within one shipment share the same `WeightUnit` and `DimensionUnit`.
    - Pickup `readyByTime < closeTime`, both today-or-future.
    - Customs invoice line items sum equals shipment declared value (within tolerance).

### Layer 3 — Server-side validation (DHL)
- **Where:** DHL's API. We do not pre-empt.
- **Why:** Things like "is this account valid", "does this postal code resolve", "is this service available on this lane", "is this HS code accepted" require DHL's own data. Replicating them client-side guarantees drift and false negatives.
- **How surfaced:** `DhlErrorMapper` translates 400 / 422 responses into `DhlValidationException` (or a subclass) carrying the DHL field paths and error codes from the response body. Consumers catch and react.

### Shared helpers — `src/Support/Assert.php`
A tiny internal helper, **not** a framework. Created lazily once we have ≥3 places repeating the same check. Expected surface (string-typed, throw `InvalidArgumentException`):
- `Assert::lengthBetween(string $value, int $min, int $max, string $fieldName): void`
- `Assert::notEmpty(string $value, string $fieldName): void`
- `Assert::oneOf(string $value, array $allowed, string $fieldName): void`
- `Assert::iso3166Alpha2(string $value, string $fieldName): void`
- `Assert::iso4217(string $value, string $fieldName): void`

No instance methods, no chains, no fluent API — utility static asserts only. This is one of the rare exceptions to the "no static methods" rule because there is no behavior to mock; these are pure functions.

### What we explicitly avoid
- ❌ JSON Schema runtime validators. The OpenAPI spec drives our PHP types directly; schema enforcement happens at compile time via PHPStan.
- ❌ A monolithic `ValidatorService` that every DTO or builder consults. Constructors and `build()` are already the validators.
- ❌ A separate "validate-only" mode. If you can construct the request, it passes our checks. Server-side outcome is the only further validation.
- ❌ Replicating server-side rules client-side just because we can. Speed of failure is rarely worth the maintenance cost of mirroring DHL's rules.

### Roadmap impact
- Phase 1 keeps doing constructor validation in VOs.
- Phase 2 introduces `Support/Assert.php` once we have repeated checks across VOs.
- Phase 3 adds `RateRequestBuilder` with cross-field rules and `InvalidRequestException`.
- Phase 4 adds `CreateShipmentBuilder` — the heaviest cross-field surface (customs, DG, VAS, weight unit consistency).
- Phase 5 adds `PickupRequestBuilder` cross-field rules (time windows).

---

## 8. Implementation Roadmap

### Phase 1 — Foundation (week 1)
**Goal:** Working scaffolding, first happy-path call.

- [x] Initialize composer.json, autoloading, basic CI
- [x] `ClientConfig`, `Credentials`, `ApiEnvironment` enum
- [x] `MessageReference`, `TrackingNumber` value objects
- [x] Base `DhlException` hierarchy (`DhlNetworkException` + `DhlApiException` with status-specific subclasses)
- [x] PSR-18 client default (Guzzle) injected through `DhlClient`; no bespoke `HttpClientInterface` — code targets `Psr\Http\Client\ClientInterface` directly via `HttpTransport`
- [x] `RequestBuilder` — standard headers (Authorization, Accept, Accept-Language, Message-Reference, x-version, optional 3PV plugin/shippingSystem/webstore)
- [x] `ResponseParser` — JSON decode + error detection (2xx → array; non-2xx routed through `DhlErrorMapper`)
- [x] `DhlErrorMapper` — HTTP status → exception subclass; raw DHL `$dhlErrorCode` carried on `DhlApiException`
- [ ] `DhlErrorCode` enum — **deferred**: cases added on demand, only when caller code wants to branch on a specific code (e.g., `9001` invalid x-version, `7012` PLT not allowed). Empty enum not created today; the raw string field is sufficient for now.
- [x] `DhlClient` facade skeleton with `tracking()` accessor
- [x] **First end-to-end test:** `TrackingApi::getByTrackingNumber()` — unit (mock client) + integration (DHL sandbox, env-gated)

### Phase 2 — Value Objects & Enums (week 1-2)
**Goal:** All foundational types in place. Split into two PRs to keep the review surface manageable.

#### Phase 2A — value objects + unit enums
- [x] Unit enums: `UnitSystem`, `WeightUnit`, `DimensionUnit`
- [x] Format-only VOs: `CountryCode` (ISO 3166-1 alpha-2), `CurrencyCode` (ISO 4217)
- [x] Measurement VOs: `Weight`, `Dimensions`, `Money`
- [x] Identity VOs: `EmailAddress`, `PhoneNumber`, `AccountNumber`, `PostalCode`, `ServiceAreaCode`, `HsCode`
- [x] PHPStan-level-8 clean on src/, level-6 clean on tests/

#### Phase 2B — DHL business enums (driven by `dhl_reference.pdf` + OpenAPI inline enums)

Shipped:
- [x] Shipment essentials: `Incoterm` (16), `PackageTypeCode` (18)
- [x] Operational: `BusinessPartyTypeCode` (6), `OtherChargeTypeCode` (15), `LandedCostRateType` (6)
- [x] Service Point / output: `DistanceUnit` (2), `ServicePointType` (4), `DayOfWeek` (8), `LabelEncodingFormat` (4)
- [x] Customs / reference: `InvoiceReferenceTypeCode` (41), `LineItemReferenceTypeCode` (43), `CustomsDocumentTypeCode` (54, single enum used at both invoice and line-item level), `RegistrationNumberTypeCode` (29), `PackageReferenceTypeCode` (14)
- [x] Dangerous goods: `DangerousGoodsContentId` (20), `DangerousGoodsServiceCode` (13)

Deferred — no authoritative source in `docs/dhl/`:
- `ContentTypeCode` (DOCUMENTS / NON_DOCUMENTS) — DHL conveys this via the boolean `isCustomsDeclarable` field, not a separate enum.
- `ProductCode`, `ServiceCode` — OpenAPI types both as free `string`; the canonical lists are not in either reference doc and must not be invented.
- `ShippingRole`, `ExportReasonType`, `TransportMode`, `PickupReason` — referenced by name in the original plan but no enumerated list found in `docs/dhl/`. Free-text fields per the OpenAPI schemas.
- `PaymentTerm` — PROJECT_PLAN sketch said `S, R, T`; OpenAPI uses `shipper, payer, duties-taxes`. Mismatched authoritative sources — keep as free string until a concrete need clarifies which one DHL actually accepts.
- `ImageOptionTypeCode` — OpenAPI has `imageOptions` as an object structure, not an enumerated type code; the `typeCode` inside is a free string.
- `HttpStatusCode` — making this an enum would brittlely constrain `DhlApiException::$httpStatus` when DHL can return any HTTP status. Raw `int` is sufficient.
- `DhlErrorCode` — already deferred in Phase 1 for the same reason (curated list added on demand).

### Phase 2C — Reference workbook reconciliation (week 2)
**Goal:** Realign the Phase 2B enums with the new authoritative source (`dhl_reference_data.xlsx`) and unblock the small set of deferred enums that now have a canonical list. **No new domain functionality** — this phase is type-system maintenance.

#### 2C.1 — Reconcile shipped enums vs xlsx
For each enum below, diff the cases against the matching xlsx sheet and ship a focused commit. Each diff is its own commit so the history shows exactly what the xlsx changed.

- [x] `CustomsDocumentTypeCode` ↔ `documentTypeCode` sheet (54 → 55: +APP, EDC, FSP, IMP, MFD, PPY; −ATR, CHD, CHP, CP2, HLC)
- [x] `InvoiceReferenceTypeCode` ↔ `invoiceReferenceType` sheet (41 → 19; PDF source carried codes that actually live at other reference levels; pruned to xlsx canon and added INB, SME, USM)
- [x] `LineItemReferenceTypeCode` ↔ `invoiceItemReferenceType` sheet (43 → 41; pruned AAM, INB)
- [x] `PackageReferenceTypeCode` ↔ `customerPackageReferenceType` sheet (14 → 81 unique typeCodes; per-country applicability is a builder/runtime concern, not encoded here)
- [x] `RegistrationNumberTypeCode` ↔ `registrationNumberTypeCode` sheet (29 → 25: +DUT, SUB; −IE, INN, KPP, MRN, OGR, OKP)
- [x] `PackageTypeCode` ↔ `packageTypeCode` sheet (18 → 22: +BB1, BB2, BB3, BB6 — bottle-box variants)
- [x] `DangerousGoodsContentId` ↔ `dangerousGoods` sheet (20 → 23 contentIds: +977, 978 sodium-ion, +YN1 tail-lift truck) and `DangerousGoodsServiceCode` (13 → 16: +HA, HB, YN)

#### 2C.2 — Ship newly-unlocked enums
The xlsx provides the authoritative list these were previously waiting on.

- [x] `ProductCode` (36 cases from `productCode` sheet)
- [x] `TrackingEventCode` (65 cases from `trackingEventCode` sheet) — will back the Phase 3 TrackingApi response DTOs
- [x] `LanguageCode` (46 unique 3-letter codes from `languageCode` sheet)
- [x] `UnitOfMeasurement` (59 codes from `unitOfMeasurement` sheet) — distinct from `WeightUnit`/`DimensionUnit`; covers customs line-item quantity units (DOZ, M3, PCS, …)

#### 2C.3 — Defer to the phase that actually consumes them
Avoid front-loading. Ship these alongside the DTOs that use them, so we have a concrete consumer to size the enum against.

- `ServiceCode` (~384 codes, grouped by `serviceGroupCode`) → **Phase 4 Shipment** (used in `ValueAddedService` payloads). Decide at that point whether to ship as one large enum or split per `serviceGroupCode` (W=Customs, H=DG, U=Temperature, etc.).
- `OutputImageTemplate` (~66 templates from `outputImageTemplate` sheet) → **Phase 4** (label generation `OutputImageProperties`).
- `CommodityCategory` (~108 codes from `commodityCategory` sheet) → **Phase 4** (export declarations).
- `ShipmentReferenceTypeCode` (~63 codes from `customerShipmentReferenceType` sheet) → **Phase 4** if `CreateShipmentBuilder` needs it; otherwise leave as free string.
- `DhlErrorCode` → still on demand, but the canonical list (`returnStatusMessage`, ~780 codes) now exists. Curated subset added when first caller wants to branch on a specific code (`9001`, `7012`, `422`, …).

#### 2C.4 — No new code for these — data, not types
- `country` (235 rows) — already covered by ISO 3166 + format-only `CountryCode` validation. Maintaining a country list is its own engineering problem (see Phase 2A decision).
- `countryPostalcodeFormat` (161 rows) — could enrich `PostalCode` with country-aware regex, but Phase 2A decision is format-only validation. If we ever revisit, this is the source.

### Phase 3 — Read-Only APIs (week 2-3)
**Goal:** All GET-style operations working. Sliced into ~4 sub-phases by complexity to keep PRs reviewable.

#### Phase 3a — Tracking + Identifier (smallest delta)
- [x] `TrackingApi::getMany()` for `GET /tracking` (multi-shipment, up to 200 numbers per call)
- [x] `IdentifierApi::allocate()` for `GET /identifiers` (SID / PID / HUID / ASID3..24)
- [x] `IdentifierType` enum (7 codes from OpenAPI inline enum)
- [x] `RequestBuilder` upgrade: list-valued query params expand into repeated `?key=v1&key=v2` pairs

#### Phase 3b — Address + Products + ReferenceData (simple GETs)
- [x] `AddressApi::validate()` for `GET /address-validate` (with `AddressValidationType` enum, `ValidatedAddress` + `ServiceArea` DTOs)
- [x] `ProductsApi::list()` for `GET /products` (minimal product DTO; breakdown / VAS tables deferred to Phase 3d alongside the rating consumers)
- [x] `ReferenceDataApi::lookup()` for `GET /reference-data` (with `ReferenceDataset` and `ComparisonOperator` enums)

#### Phase 3c — ServicePoint + Epod (medium)
- [x] `ServicePointApi::find()` for `GET /servicepoints` (subset DTO: facility id, address, geo, opening hours; reuses existing `ServicePointType` and `DayOfWeek` enums via `tryFrom` with raw-string fallback for unknown wire values)
- [x] `EpodApi::get()` for `GET /shipments/{id}/proof-of-delivery` (JSON response with base64-encoded documents; `EpodContent` enum covers the 7 content variants)

#### Phase 3d — Rates + LandedCost (complex)
- [x] `RatesApi::quote()` for `GET /rates` (single-piece, query-string) and `RatesApi::quoteMany()` for `POST /rates` (multi-piece, JSON body via `RateRequest` DTO; the OpenAPI spec calls the multi-piece operation `exp-api-rates-many`, hence the helper name — there is no separate `/rates-many` endpoint)
- [x] `LandedCostApi::estimate()` for `POST /landed-cost`. Response shape (`supermodelIoLogisticsExpressRates`) is shared with `/rates`, so a single `RatesResponseHydrator` covers all three. Per-line-item charge breakdowns surface as `QuotedProduct::$rawItems` — typed children can fan out when a concrete consumer surfaces.
- [x] `RateRequestBuilder` and `LandedCostRequestBuilder` — first builders in the project; both accumulate cross-field errors and throw a single `InvalidRequestException` (per §7).

#### Cross-cutting
- [x] DTOs for all responses (per sub-phase) — Tracking, Identifier, Address, Products, ReferenceData, Epod, ServicePoint all shipped with typed response DTOs
- [x] Unit tests per API (mocked HTTP) — every shipped `*Api` has a sibling `*ApiTest` under `tests/Unit/Api/` exercising hydration + query-parameter emission against `php-http/mock-client`
- [x] Integration tests against sandbox — covers Tracking (single + multi), Address, ReferenceData, Products, Identifier, Epod (404 path), ServicePoint (Brussels + enum drift detection). Shared `IntegrationTestCase` base with env-gated `makeClient()` / `requireAccountNumber()` helpers and `nextBusinessDay()` for date-stable lookups.

### Phase 4 — Shipment Creation (week 3-4) — biggest feature
**Goal:** Create a real shipment end to end.

This is the largest sub-phase in the project. Phase 3d already shipped as one combined ~50-file PR; Phase 4 is wider still, so the recommended slice is three sequential PRs. Each one delivers something usable on its own.

#### Phase 4a — Foundation: happy-path shipment creation
- [ ] Common shipment DTOs: `ContactAddress` (Address + Contact composite), `Package` (the full shipment-side package, distinct from `RatePackage`), `OutputImageProperties`, `ValueAddedService`, `Invoice`, `LineItem` (shipment line item, distinct from the landed-cost one)
- [ ] `CreateShipmentRequest` (top-level) + `CreateShipmentResponse`
- [ ] `CreateShipmentBuilder` — second builder. Cross-field rules that ship in 4a: weight/dimension unit consistency across packages, `isCustomsDeclarable=true` ⇒ `exportDeclaration` required, DDP incoterm ⇒ payer details required.
- [ ] `ShipmentApi::create()` for `POST /shipments`
- [ ] Wire `shipments()` accessor on `DhlClient`
- [ ] Integration test: domestic non-customs shipment happy path

#### Phase 4b — Customs / DG / PLT
- [ ] `ExportDeclaration` deepening (line-item totals, declared-value reconciliation, EU intra-zone exemption table)
- [ ] `DangerousGoods` block + builder rule (DG VAS code present ⇒ DG block required)
- [ ] Insurance VAS rule (`II` ⇒ declared `Money` required)
- [ ] Paperless Trade flow: `ShipmentApi::uploadImage()` for `POST /shipments/{id}/upload-image`, `ShipmentApi::uploadInvoiceData()` for `POST /shipments/{id}/upload-invoice-data`, `ShipmentApi::getImage()` for `GET /shipments/{id}/get-image`
- [ ] Customs invoice line-item sum reconciliation in builder
- [ ] Integration test: cross-border customs-declarable shipment with PLT

#### Phase 4c — Add-piece + label format polish
- [ ] `ShipmentApi::addPiece()` for `POST /shipments/{id}/add-piece`
- [ ] Output image format coverage — PDF/ZPL/EPL/LP2 paths in `OutputImageProperties`
- [ ] Label-template support via the deferred `OutputImageTemplate` enum (~66 templates from `outputImageTemplate` xlsx sheet — ship now that there's a concrete consumer)

#### Phase 4 cross-cutting — enums unblocked by a concrete consumer
Per Phase 2C.3, these were deferred until something used them. Phase 4 is that consumer:
- [ ] `ServiceCode` (~384 codes from `serviceCode` xlsx sheet) — decision point: ship as one large enum or split per `serviceGroupCode` (W=Customs, H=DG, U=Temperature, …). Recommend deciding when 4b lands.
- [ ] `OutputImageTemplate` (~66 templates) — ships in 4c.
- [ ] `CommodityCategory` (~108 codes from `commodityCategory` sheet) — ships in 4b alongside `ExportDeclaration`.
- [ ] `ShipmentReferenceTypeCode` (~63 codes from `customerShipmentReferenceType` sheet) — ship if `CreateShipmentBuilder` needs it; otherwise leave as free string.

### Phase 5 — Pickup & Operations (week 4-5)
- [ ] `PickupApi` (create, update, cancel, list)
- [ ] `PickupRequestBuilder`
- [ ] `EarlyShipmentScreeningApi`
- [ ] Round-trip integration test: create shipment → schedule pickup → cancel pickup

### Phase 6 — Polish & Release (week 5-6)
- [ ] Full README with usage examples
- [ ] CHANGELOG.md
- [ ] `examples/` directory with runnable scripts
- [ ] CI/CD via GitHub Actions
- [ ] Tag v0.1.0 → publish on Packagist
- [ ] Documentation site (optional, e.g. with VuePress or Doctum)

---

## 9. Testing Strategy

### Unit tests
- Run on every commit, every PR
- Use mocked HTTP client (`php-http/mock-client`)
- JSON fixtures stored in `tests/Fixtures/`
- Target: ≥90% line coverage on `src/`

### Integration tests
- Tagged with `@group integration`
- Skipped by default unless `DHL_API_KEY` env vars are set
- Hit real DHL sandbox (`https://express.api.dhl.com/mydhlapi/test`)
- Run nightly via scheduled GitHub Actions
- Use real test account number provided by DHL

### Test categories
1. **Value Object tests** — construction, validation, equality
2. **Enum tests** — case coverage, helper methods
3. **DTO tests** — `toArray()` output matches DHL schema
4. **Builder tests** — fluent chains produce correct DTOs
5. **API class tests** — request building + response parsing with mocked HTTP
6. **Exception tests** — error mapping is correct
7. **Integration tests** — real sandbox calls

---

## 10. Coding Conventions

- **PSR-12** code style enforced via `php-cs-fixer` (config: `.php-cs-fixer.php`)
- **PHPStan level 8** — no `mixed`, all return types declared
- **Strict types** — `declare(strict_types=1);` at top of every file
- **Final classes** by default — only mark non-final when designed for extension
- **Readonly** for all DTOs and Value Objects
- **Constructor property promotion** for all DTOs/VOs
- **Named arguments** in all public API calls (better DX)
- **No abbreviations** — `getTrackingStatus()` not `getTrkStatus()`
- **DHL terminology** preserved — `Shipment`, `Piece`, `Waybill`, `ServicePoint`, etc.
- **Conventional Commits** for all git messages: `feat:`, `fix:`, `chore:`, `docs:`, `test:`, `refactor:`

---

## 11. Future Considerations (post v1.0)

- **PSR-6/PSR-16 cache** — cache reference data lookups
- **Async/promises** — Guzzle async for parallel requests (multi-rates)
- **Webhook receiver helpers** — DHL ODD callbacks
- **Symfony bundle** — wrapper package `medzuch/dhl-express-symfony-bundle`
- **Laravel package** — wrapper `medzuch/dhl-express-laravel`
- **CLI tool** — diagnostic CLI for testing API access

---

## 12. CI/CD & Deployment (later phase)

When the library matures, the user has plans to build an app on top of it. That app will use:
- **GitHub Actions** for CI
- **Docker images** pushed to GCR or Amazon ECR
- **Kubernetes** on GKE (Google) or EKS (AWS)
- **JetBrains Gateway** for remote dev (optional)

The library itself only needs:
- GitHub Actions workflow to run tests on PRs (PHP 8.3 matrix)
- Auto-publish to Packagist on tagged releases
- Optional Codecov for coverage badges

---

## 13. Reference Materials Stored Locally

Place these in the project's `docs/dhl/` folder for offline reference (precedence — primary first):
- `dhl_reference_data.xlsx` — Machine-readable workbook from DHL's Reference Data API. **Authoritative source for value lists.**
- `dhl_openapi.yaml` — Full OpenAPI spec (request/response shapes and field constraints).
- `dhl_reference.pdf` — Narrative reference; secondary because it carries legacy codes the live API no longer accepts.

When generating enums, ALWAYS cross-reference these files. When the xlsx and the PDF disagree, the xlsx wins.

---

## 14. Key DHL Concepts Glossary

| DHL Term | Meaning |
|---|---|
| **Shipment** | A complete delivery transaction (one or more pieces) |
| **Piece / Package** | A single physical package within a shipment |
| **Waybill** | The shipment tracking document/number |
| **AWB** | Air Waybill — primary tracking number |
| **MAWB** | Master Air Waybill (consolidation) |
| **HWB** | House Waybill |
| **PLT** | Paperless Trade — electronic customs documents |
| **EPOD** | Electronic Proof of Delivery |
| **VAS** | Value Added Service (insurance, signature, etc.) |
| **ODD** | On-Demand Delivery |
| **Service Point** | DHL pickup/dropoff location |
| **Incoterm** | International commerce term (EXW, DDP, etc.) |
| **HS Code** | Harmonized System code for customs |
| **EORI** | Economic Operator Registration ID |

---

## 15. Status & Decisions Log

| Date | Decision | Rationale |
|---|---|---|
| 2026-05-03 | PHP 8.3 (not 8.5) | 8.3 is stable, EOL Dec 2027 |
| 2026-05-03 | No framework, pure library | Maximum reusability across Symfony/Laravel/standalone |
| 2026-05-03 | PSR-18 as core abstraction from day 1 | Guzzle ships as default implementation; users can inject any PSR-18 client without library changes |
| 2026-05-03 | Debian-based PHP image | Better toolchain compatibility than Alpine |
| 2026-05-03 | Symfony 8.1 deferred | Will be used in app layer later, not the library |
| 2026-05-03 | DHL Express API 3.2.2 | Latest published version (Apr 2026) |
| 2026-05-03 | Three-layer validation (constructors / builders / DHL) | Constructors prove type validity, builders enforce cross-field rules with accumulated errors, server-side rules stay on DHL — no JSON Schema runtime, no monolithic validator service |
| 2026-05-04 | Three-enum split for unit handling: `UnitSystem` + `WeightUnit` + `DimensionUnit` | DHL's wire format only carries `unitOfMeasurement: metric\|imperial` at the shipment level. The earlier sketch `enum WeightUnit { case KG = 'metric' }` conflated unit symbol and system. Splitting them keeps backing values matching their semantic meaning (`WeightUnit::KG->value === 'KG'`) and lets the future `CreateShipmentBuilder` enforce shipment-wide consistency by reading `->system()` on each value, with one source of truth per concept. |
| 2026-05-04 | Format-only validation for `CountryCode` / `CurrencyCode` | Maintaining ~250 ISO 3166 / ~180 ISO 4217 codes client-side is its own engineering problem and DHL already rejects unknown codes with a 400 → `DhlValidationException`. Format check (regex) plus server-side validation is sufficient. |
| 2026-05-04 | Single `CustomsDocumentTypeCode` instead of separate invoice/line-item enums | OpenAPI uses identical value spaces for the customs-document field at both invoice level (Reference Data Guide section 10) and line-item level (section 12); a single enum is the simpler model. Original PROJECT_PLAN sketched two separate enums but that would have duplicated 54 cases for no observable benefit. |
| 2026-05-04 | Defer enums without authoritative source: `ContentTypeCode`, `ProductCode`, `ServiceCode`, `ShippingRole`, `ExportReasonType`, `TransportMode`, `PaymentTerm`, `PickupReason`, `ImageOptionTypeCode`, `HttpStatusCode` | CLAUDE.md is unambiguous: do not invent values. These names appeared in early planning but their value lists are not in either `dhl_openapi.yaml` (typed as free `string`) or `dhl_reference.pdf` (no section). `HttpStatusCode` is a separate case — making it an enum would brittlely constrain the existing `int $httpStatus` field on `DhlApiException`. All remain free strings or raw types until a concrete consumer surfaces with a definitive source. |
| 2026-05-05 | Adopt `dhl_reference_data.xlsx` as the **primary** authoritative source for value lists; PDF demoted to secondary | The xlsx is a direct dump of DHL's Reference Data API across 19 sheets and is more current than the PDF (which carries legacy/deprecated codes the live API no longer accepts). When the two disagree, xlsx wins. PDF stays around for narrative context and as a secondary sanity check. |
| 2026-05-05 | Insert Phase 2C (workbook reconciliation) before Phase 3 | The xlsx changes the source of truth for several already-shipped enums (notably `InvoiceReferenceTypeCode` 41→19 and `PackageReferenceTypeCode` 14→~30 unique). Reconciling now keeps Phase 3+ DTOs from being built on enums that disagree with the live API, and unblocks `ProductCode`, `TrackingEventCode`, `LanguageCode`, `UnitOfMeasurement` which were deferred for lack of a canonical list. Larger lists (`ServiceCode` ~384, `OutputImageTemplate`, `CommodityCategory`) stay deferred to the phase that consumes them so we have a concrete sizing target. |
| 2026-05-05 | Keep `DhlErrorCode` deferred even though canonical list now exists (~780 entries in `returnStatusMessage`) | The original deferral rationale was "no source"; with a canonical list, the rationale shifts to "no caller yet branches on a specific code". Raw `$dhlErrorCode` string on `DhlApiException` remains sufficient. We curate the enum incrementally as concrete error-handling needs surface. Encoding 780 cases speculatively would be expensive maintenance for unclear benefit. |
| 2026-05-06 | PSR-3 logger wired through `HttpTransport`, surfaced via `DhlClient` constructor | Added `psr/log` ^3.0 as a runtime dep so callers can plug a logger to see request/response payloads. Defaults to `NullLogger` (zero cost when absent). `Authorization` header is redacted before logging so basic-auth credentials never leak. Picked `HttpTransport` as the single chokepoint instead of per-`*Api` instrumentation or a PSR-18 decorator — minimum surface, maximum coverage. |
| 2026-05-06 | Wire `.env` into the container via `env_file:` and add `make setup` bootstrap | `docker compose` reads `.env` for compose-file substitution but does **not** pass it into the container without `env_file:`. Adding it makes integration tests (and any in-container script) pick up `DHL_API_KEY` etc. via `getenv()` the canonical way. `make setup` chains `cp -n .env.example .env` + build + composer install so onboarding is one command. `cp -n` keeps re-runs idempotent. `.env.example` carries a top-of-file note that values must not be quoted — `env_file:` passes quote characters through verbatim. |
| 2026-05-06 | Integration tests log request/response payloads to `var/integration-logs/` | Hand-rolled `JsonLineFileLogger` in `tests/Integration/Logging/` (extends `Psr\Log\AbstractLogger`, ~50 lines) wired into `IntegrationTestCase::makeClient()`. One file per test method, JSONL format, truncated on first write per run. Hand-rolled instead of pulling in monolog because: zero new dev deps, the library's "no framework" ethos, and the requirement is just append-only file output for sandbox debugging. |
| 2026-05-08 | Phase 3d shipped as a single combined PR (Rates + LandedCost) | Both endpoints share request building blocks (`Account`, `RateAddress`, `RatePackage`, `CustomerDetails`) and the entire response model (`supermodelIoLogisticsExpressRates`). Splitting into two PRs would have made PR 3d-2 reach back into 3d-1's surface anyway. User opted for one combined ~50-file PR instead of two smaller ones; tradeoff is a wider review surface in exchange for atomic delivery of the rating domain. |
| 2026-05-08 | First builders + `InvalidRequestException` land in Phase 3d | The §7 validation strategy specifies: constructors validate single-field rules; builders accumulate cross-field rules and throw `InvalidRequestException` with every issue at once. `RateRequestBuilder.build()` and `LandedCostRequestBuilder.build()` are the first concrete implementations. The cross-field rule that ships first is "every package's weight/dimension unit system must match the request-level `unitOfMeasurement`" — a canonical example of a rule DHL would otherwise reject server-side; we surface it locally with field paths. |
| 2026-05-08 | One `RatesResponseHydrator` shared by `/rates` and `/landed-cost` | Both endpoints return `supermodelIoLogisticsExpressRates`. Duplicating the ~250 lines of hydration in two API classes was rejected; instead the hydrator lives in `src/Dto/Rate/` (pure data-shape concern) and is injected by default into both `RatesApi` and `LandedCostApi` constructors. Per-line-item charge breakdowns from `/landed-cost` surface as `QuotedProduct::$rawItems` rather than a typed child class — they will get typed when a concrete consumer surfaces a use case. |
| 2026-05-08 | DHL has no `/rates-many` endpoint — earlier PROJECT_PLAN entry was incorrect | The OpenAPI spec defines `GET /rates` (operationId `exp-api-rates`, single-piece query-string) and `POST /rates` (operationId `exp-api-rates-many`, multi-piece JSON body). The PROJECT_PLAN domain table erroneously listed `POST /rates-many` as a separate endpoint; that's the operation ID, not the URL. Corrected in the §3 endpoint table during Phase 3d. |
| 2026-05-08 | DHL "API Platform Modernization" email audit — library unaffected | Email from DHL devportal announces three platform-level changes: (1) HTTP response header names switching to lower-case on **28 May 2026** (HTTP/2 standard); (2) new IP allow-list addresses **22-29 June 2026**; (3) cipher-suite tightening to TLS 1.3 + TLS 1.2 ECDHE-RSA-AES-GCM only **22-29 June 2026**. Audit result: no library code changes needed. `HttpTransport::redactHeaders()` already uses `strcasecmp()`; `ResponseParser` doesn't read headers; all tests use PSR-7 `getHeaderLine()` (case-insensitive by spec). IP / cipher concerns are deployment-environment problems for the consumer, not the library — we use the domain `https://express.api.dhl.com/...` and Guzzle delegates TLS to PHP's curl/OpenSSL stack, which on PHP 8.3 + a modern OS already negotiates TLS 1.3 by default. Recommended follow-up: re-run `make test-integration` after 28 May to verify no latent issue surfaces. Email saved at `docs/dhl/Important_ Upcoming Modernization of DHL's API Platform.eml` for archival reference. |
| 2026-05-08 | Phase 4 to be sliced into three sequential PRs (4a/4b/4c) | Phase 3d already shipped as a single ~50-file PR; Phase 4 has more surface area and would balloon further if landed as one. 4a = foundation (happy-path domestic shipment + builder + `ShipmentApi::create()`). 4b = customs / dangerous-goods / paperless-trade. 4c = add-piece + label-format polish. Each slice ships something usable on its own and unblocks the deferred Phase 2C.3 enums (`ServiceCode`, `OutputImageTemplate`, `CommodityCategory`) at the slice that consumes them. See §8 Phase 4 sub-phases for the per-slice breakdown. |

---

**End of plan.** Update this document as decisions evolve. When starting a new Claude session, share this file to restore full context.

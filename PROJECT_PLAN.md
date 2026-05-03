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
| HTTP client | Guzzle 7.9 | `guzzlehttp/guzzle` + `guzzlehttp/psr7` |
| Testing | PHPUnit 11 | Unit + Integration suites |
| Static analysis | PHPStan 2.0 (level 8) | Strictest level |
| Code style | php-cs-fixer 3.x | PSR-12 + PHP 8.3 migration rules |
| HTTP mocking | `php-http/mock-client` + `nyholm/psr7` | For unit tests |

### Daily Workflow
```bash
make up         # start containers
make install    # composer install
make test       # phpunit
make analyse    # phpstan
make check      # test + analyse
make shell      # enter container
make down       # stop
```

---

## 3. DHL Express API Overview

**API version:** MyDHL API 3.2.2 (release date 5 Apr 2026)
**Spec:** OpenAPI 3.0.0
**Authentication:** HTTP Basic Auth (username + password)
**Content type:** `application/json`
**Reference docs:** Both the OpenAPI YAML and the Reference Data PDF (3.2.2) are stored in `/docs/dhl/` for reference.

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
| **Rates** | `POST /rates`, `POST /rates-many` | Get shipping rate quotes |
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
8. **HTTP client interface** — abstraction over Guzzle for testability
9. **Ubiquitous Language** — mirror DHL's own terminology (Shipment, Piece, Waybill, ServicePoint, Pickup)

### Anti-patterns to AVOID
- ❌ God class — `DhlClient` delegates, never implements
- ❌ Raw arrays for API data
- ❌ Leaking Guzzle exceptions
- ❌ Hardcoded URLs/timeouts/credentials
- ❌ Static methods (untestable, can't mock)
- ❌ Premature abstraction (PSR-18 layer can wait)
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
│   │   ├── HttpClientInterface.php             # PSR-18-style abstraction
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
│   │   ├── Incoterm.php                        # EXW, FCA, CPT, CIP, DPU, DAP, DDP, etc.
│   │   ├── PackageTypeCode.php                 # TBS, 1CE, CE1, 2BC, XPD, etc.
│   │   ├── WeightUnit.php                      # KG, LB
│   │   ├── DimensionUnit.php                   # CM, IN
│   │   ├── ProductCode.php                     # P, D, K, T, etc.
│   │   ├── ServiceCode.php                     # WY, PK, PT, PU, etc. (from VAS list)
│   │   ├── BusinessPartyTypeCode.php           # BU, DC, GV, OT, PR, RE
│   │   ├── DangerousGoodsContentId.php         # 650, A01, 651, etc.
│   │   ├── DangerousGoodsServiceCode.php       # HY, HL, HN, etc.
│   │   ├── RegistrationNumberTypeCode.php      # CNP, DAN, EOR, VAT, EIN, etc.
│   │   ├── InvoiceReferenceTypeCode.php        # ACL, CID, CN, CU, ITN, MRN, etc.
│   │   ├── InvoiceCustomsDocumentTypeCode.php  # 972, AHC, ATA, ATR, etc.
│   │   ├── LineItemReferenceTypeCode.php       # AFE, AAJ, ABW, ALX, etc.
│   │   ├── LineItemCustomsDocumentTypeCode.php # Same as invoice + variants
│   │   ├── OtherChargeTypeCode.php             # ADMIN, DELIV, DOCUM, etc.
│   │   ├── LandedCostRateType.php              # default_rate, derived_rate, etc.
│   │   ├── ExportReasonType.php                # PERSONAL, COMMERCIAL, GIFT
│   │   ├── TransportMode.php                   # AIR, OCEAN, LAND
│   │   ├── ShippingRole.php                    # shipper, receiver, payer, buyer, seller, importer, exporter, broker
│   │   ├── PaymentTerm.php                     # S, R, T (shipper, receiver, third party)
│   │   ├── PickupReason.php                    # closed, late, etc.
│   │   ├── ImageOptionTypeCode.php             # invoice, label, receipt, shipmentReceipt, etc.
│   │   ├── ImageEncodingFormat.php             # PDF, ZPL, EPL, LP2, TIFF, PNG, JPEG
│   │   ├── ContentTypeCode.php                 # NON_DOCUMENTS, DOCUMENTS
│   │   ├── ApiEnvironment.php                  # SANDBOX, PRODUCTION
│   │   └── HttpStatusCode.php                  # 200, 201, 400, 401, 403, 404, 422, 500
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

enum WeightUnit: string {
    case KG = 'metric';   // KG in DHL API
    case LB = 'imperial'; // LB in DHL API
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

## 7. Implementation Roadmap

### Phase 1 — Foundation (week 1)
**Goal:** Working scaffolding, first happy-path call.

- [ ] Initialize composer.json, autoloading, basic CI
- [ ] `ClientConfig`, `Credentials`, `ApiEnvironment` enum
- [ ] `HttpClientInterface` + `GuzzleHttpClient`
- [ ] `RequestBuilder` — standard headers (Message-Reference, x-version, Accept-Language)
- [ ] `ResponseParser` — JSON decode + error detection
- [ ] Base `DhlException` hierarchy
- [ ] `DhlErrorCode` enum (all 200+ error codes from PDF)
- [ ] `DhlErrorMapper` — HTTP status + DHL error code → exception
- [ ] `DhlClient` facade skeleton
- [ ] **First end-to-end test:** `TrackingApi::getStatus()` against sandbox

### Phase 2 — Value Objects & Enums (week 1-2)
**Goal:** All foundational types in place.

- [ ] All `Enum/` classes (Incoterm, PackageTypeCode, WeightUnit, etc. — ~25 enums)
- [ ] All `ValueObject/` classes with validation
- [ ] PHPStan-level-8 clean
- [ ] 100% test coverage on VOs and Enums

### Phase 3 — Read-Only APIs (week 2-3)
**Goal:** All GET-style operations working.

- [ ] `TrackingApi` (single + multi tracking)
- [ ] `IdentifierApi`
- [ ] `AddressApi` (validate)
- [ ] `ProductsApi`
- [ ] `RatesApi` + `LandedCostApi`
- [ ] `EpodApi`
- [ ] `ServicePointApi`
- [ ] `ReferenceDataApi`
- [ ] DTOs for all responses
- [ ] Unit tests per API + integration tests against sandbox

### Phase 4 — Shipment Creation (week 3-4) — biggest feature
**Goal:** Create a real shipment end to end.

- [ ] All shipment-related DTOs (~15 classes)
- [ ] `CreateShipmentBuilder` — fluent interface for the deeply nested request
- [ ] `ShipmentApi::create()`
- [ ] `ShipmentApi::addPiece()`
- [ ] `ShipmentApi::uploadImage()`
- [ ] `ShipmentApi::uploadInvoiceData()`
- [ ] `ShipmentApi::getImage()`
- [ ] `ExportDeclaration` and customs handling
- [ ] `DangerousGoods` support
- [ ] PaperlessTrade (PLT) flow
- [ ] Output image options (PDF/ZPL/EPL labels)
- [ ] Integration tests with full create-shipment workflow

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

## 8. Testing Strategy

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

## 9. Coding Conventions

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

## 10. Future Considerations (post v1.0)

- **PSR-18 HTTP adapter** — let consumers swap Guzzle for any PSR-18 client
- **PSR-3 logger integration** — optional logger injection for request/response logging
- **PSR-6/PSR-16 cache** — cache reference data lookups
- **Async/promises** — Guzzle async for parallel requests (multi-rates)
- **Webhook receiver helpers** — DHL ODD callbacks
- **Symfony bundle** — wrapper package `medzuch/dhl-express-symfony-bundle`
- **Laravel package** — wrapper `medzuch/dhl-express-laravel`
- **CLI tool** — diagnostic CLI for testing API access

---

## 11. CI/CD & Deployment (later phase)

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

## 12. Reference Materials Stored Locally

Place these in the project's `docs/dhl/` folder for offline reference:
- `dhl_reference.pdf` — All reference data codes (incoterms, packages, errors, etc.)
- `dhl_openapi.yaml` — Full OpenAPI spec

When generating enums, ALWAYS cross-reference both files to ensure values are correct and complete.

---

## 13. Key DHL Concepts Glossary

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

## 14. Status & Decisions Log

| Date | Decision | Rationale |
|---|---|---|
| 2026-05-03 | PHP 8.3 (not 8.5) | 8.3 is stable, EOL Dec 2027 |
| 2026-05-03 | No framework, pure library | Maximum reusability across Symfony/Laravel/standalone |
| 2026-05-03 | Guzzle over PSR-18 adapter | Simpler for v1, can abstract later |
| 2026-05-03 | Debian-based PHP image | Better toolchain compatibility than Alpine |
| 2026-05-03 | Symfony 8.1 deferred | Will be used in app layer later, not the library |
| 2026-05-03 | DHL Express API 3.2.2 | Latest published version (Apr 2026) |

---

**End of plan.** Update this document as decisions evolve. When starting a new Claude session, share this file to restore full context.

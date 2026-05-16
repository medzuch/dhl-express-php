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
| Testing | PHPUnit 12 | Unit + Integration suites |
| Static analysis | PHPStan 2.x (^2.1, level max) | Strictest level; separate config for tests at level 6 |
| Code style | php-cs-fixer 3.x | PSR-12 + PHP 8.3 migration rules |
| HTTP mocking | `php-http/mock-client` + `nyholm/psr7` | For unit tests |

### Daily Workflow
```bash
make up             # start containers
make install        # composer install
make test               # phpunit unit suite only (integration excluded by default)
make test-integration   # phpunit integration suite (requires DHL_API_KEY)
make analyse            # phpstan analyse src/ (level max)
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
- **PHPStan level max** from day one — no `mixed` types unless absolutely necessary
- All public APIs must have full type declarations and docblocks
- Constructor property promotion + readonly everywhere DTOs are used

---

## 5. Folder Structure

The full layout is derivable from `ls -R src/`. Top-level shape only:

```
dhl-express-php/
├── docs/
│   ├── dhl/                  # DHL reference workbook + OpenAPI + PDF + announcements
│   ├── DECISIONS.md          # full architecture decision log
│   ├── PROJECT_HISTORY.md    # completed-phase checklists
│   └── ROADMAP.md            # post-1.0 / CI-CD plans
├── src/
│   ├── DhlClient.php         # main facade
│   ├── ClientConfig.php
│   ├── Api/                  # one class per DHL domain (TrackingApi, RatesApi, …)
│   ├── Auth/                 # Credentials value object
│   ├── Builder/              # cross-field validating request builders
│   ├── Dto/                  # request + response DTOs grouped by domain
│   ├── Enum/                 # backed string enums for DHL code lists
│   ├── Exception/            # DhlException hierarchy + ErrorCode mapper
│   ├── Http/                 # PSR-18 transport, RequestBuilder, ResponseParser
│   └── ValueObject/          # self-validating types (Weight, Money, CountryCode, …)
├── tests/
│   ├── Unit/                 # mocked HTTP, mirrors src/ layout
│   ├── Integration/          # real sandbox calls (env-gated by DHL_API_KEY)
│   └── Fixtures/             # JSON fixtures captured from DHL responses
├── examples/                 # runnable usage examples
└── PROJECT_PLAN.md           # this file
```

The live state of `src/` is the source of truth — when adding a new domain,
follow the patterns of an existing one (e.g. `src/Api/RatesApi.php` +
`src/Dto/Rate/`) rather than updating an inventory here.

---

## 6. Key Type Design Decisions

The patterns below are now embodied in shipped code — point at the canonical
example for each rather than reading prose here.

- **Value Objects** — `final readonly class`, constructor enforces format,
  throws `\InvalidArgumentException` on bad input, helper methods for
  conversions. Canonical example: `src/ValueObject/Weight.php` (value + unit
  + `inKilograms()`); see also `src/ValueObject/Money.php`,
  `src/ValueObject/TrackingNumber.php`.
- **Backed string enums** — case backing value matches the DHL wire format;
  add helper methods (`->system()`, `->description()`) when callers need to
  branch. Canonical example: `src/Enum/WeightUnit.php` paired with
  `src/Enum/UnitSystem.php`; see also `src/Enum/Incoterm.php`.
- **DTOs** — `final readonly class`, constructor property promotion, named
  arguments at call sites, `\DateTimeImmutable` for dates, typed arrays via
  `@var` docblocks. Canonical example: `src/Dto/Rate/RateRequest.php`.
- **Builders** — accumulate cross-field errors and throw a single
  `InvalidRequestException` with field paths (per §7). Canonical example:
  `src/Builder/RateRequestBuilder.php`.

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

### Completed phases

Per-item checklists for shipped phases live in [`docs/PROJECT_HISTORY.md`](docs/PROJECT_HISTORY.md).

- ✅ **Phase 1 — Foundation** — shipped 2026-05-04, PR #1 (`02102c6`). Open `DhlErrorCode` enum still deferred (added on demand).
- ✅ **Phase 2A — Value objects + unit enums** — shipped 2026-05-04, PR #2 (`81ccaf7`).
- ✅ **Phase 2B — DHL business enums** — shipped 2026-05-05, PR #3 (`d0b1c55`).
- ✅ **Phase 2C — Reference workbook reconciliation** — shipped 2026-05-05, PR #5 (`b066283`). Unlocked `ProductCode`, `TrackingEventCode`, `LanguageCode`, `UnitOfMeasurement`. `ServiceCode` / `OutputImageTemplate` / `CommodityCategory` / `ShipmentReferenceTypeCode` still deferred to Phase 4.
- ✅ **Phase 3a — Tracking + Identifier** — shipped 2026-05-05, PR #6 (`46f0a9c`).
- ✅ **Phase 3b — Address + Products + ReferenceData** — shipped 2026-05-05, PR #7 (`b4d3ee4`).
- ✅ **Phase 3c — ServicePoint + Epod** — shipped 2026-05-08, PR #11 (`13fe7fa`).
- ✅ **Phase 3d — Rates + LandedCost** — shipped 2026-05-08, PR #12 (`1a1c56b`). First builders + `InvalidRequestException` (see §7).
- ✅ **Phase 4a — Shipment foundation** — shipped 2026-05-09, PR #16 (`1a39f51`). `ContactAddress`, `Package`, `OutputImageProperties`, `ValueAddedService`, `CreateShipmentRequest/Response`, `CreateShipmentBuilder`, `ShipmentApi::create()`.
- ✅ **Phase 4b — Customs / DG / PLT** — shipped 2026-05-09, PR #17 (`0988bc1`) + fix PR #19 (`7812bcc`). `ExportDeclaration`, `DangerousGoods`, PLT upload endpoints, all builder cross-field rules (DG VAS, insurance VAS, DDP incoterm, EU customs territory + NI BT-postcode detection, line-item reconciliation).
- ✅ **Phase 4c — Add-piece + label polish** — shipped 2026-05-09, PR #18 (`39c1a19`). `ShipmentApi::addPiece()`, `OutputImageTemplate` (35 templates), `ServiceCode` (383 codes), `CommodityCategory` (108 codes), `ShipmentReferenceTypeCode`.
- ✅ **Phase 4d — Invoices + ServicePoint expansion + EarlyShipmentScreening** — shipped 2026-05-15. `InvoiceApi::uploadInvoiceData()` (standalone `POST /invoices/upload-invoice-data`); `UploadInvoiceDataRequest` extended with `outputImageProperties` and 7-role `customerDetails` (seller/buyer/importer/exporter/manufacturer/ultimateConsignee/broker); `ServicePointApi::find()` refactored to `ServicePointFindCriteria` + `ServicePointFindCriteriaBuilder` exposing the full 30+ query-parameter surface (breaking change); `EarlyShipmentScreeningApi::screen()` for BBX baby-shipment Denied Party screening. New enums: `InvoicePartyTypeCode`, `WeightUom`, `DimensionsUom`, `ResultUom`, `ServicePointCapability`, `ServicePointStatus`, `ServicePointOpenDay`, `YesNoIndicator`, `TrueFalseFlag`.

### Phase 5 — Pickup & Operations (week 4-5)
- [ ] `PickupApi` (create, update, cancel, list)
- [ ] `PickupRequestBuilder`
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
- **PHPStan level max** — no `mixed`, all return types declared
- **Strict types** — `declare(strict_types=1);` at top of every file
- **Final classes** by default — only mark non-final when designed for extension
- **Readonly** for all DTOs and Value Objects
- **Constructor property promotion** for all DTOs/VOs
- **Named arguments** in all public API calls (better DX)
- **No abbreviations** — `getTrackingStatus()` not `getTrkStatus()`
- **DHL terminology** preserved — `Shipment`, `Piece`, `Waybill`, `ServicePoint`, etc.
- **Conventional Commits** for all git messages: `feat:`, `fix:`, `chore:`, `docs:`, `test:`, `refactor:`

---

## 11. Beyond v0.1.0

Post-1.0 considerations (cache, async, framework bundles, CLI) and the CI/CD
plans live in [`docs/ROADMAP.md`](docs/ROADMAP.md). Not loaded into Claude
sessions by default — `@`-reference it on demand when the work shifts to
release-engineering territory.

---

## 12. Reference Materials Stored Locally

Place these in the project's `docs/dhl/` folder for offline reference (precedence — primary first):
- `dhl_reference_data.xlsx` — Machine-readable workbook from DHL's Reference Data API. **Authoritative source for value lists.**
- `dhl_openapi.yaml` — Full OpenAPI spec (request/response shapes and field constraints).
- `dhl_reference.pdf` — Narrative reference; secondary because it carries legacy codes the live API no longer accepts.

When generating enums, ALWAYS cross-reference these files. When the xlsx and the PDF disagree, the xlsx wins.

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

Full decision log lives in [`docs/DECISIONS.md`](docs/DECISIONS.md). The two most
recent entries are inlined below; older rationale is preserved verbatim there.

| Date | Decision | Rationale |
|---|---|---|
| 2026-05-15 | Phase 4d `ServicePointApi::find()` swapped from named arguments to a `ServicePointFindCriteria` + `ServicePointFindCriteriaBuilder` pair | The spec exposes 30+ optional query parameters on `/servicepoints`; carrying them all as named-argument scalars produced a 30-line signature that's painful to call and hides cross-field rules (search-mode mutual exclusivity, `weight`+`weightUom` pairing, `HH:MM` time format). A criteria DTO + builder matches the pattern already used for rates/landed-cost/shipment and centralises the cross-field validation. Accepted as a breaking change because this is pre-1.0 and the surface change is straightforward to migrate. |
| 2026-05-15 | Invoice-upload `customerDetails` uses lowercase `typeCode` enum (`InvoicePartyTypeCode`) distinct from shipment-level `BusinessPartyTypeCode` | DHL maintains two parallel vocabularies for business-party types: shipment-level uses two-letter codes (`BU`, `DC`, …) while invoice-upload uses lowercase full words (`business`, `direct_consumer`, …). Same conceptual slot, different wire format. Mirroring DHL's split keeps wire-format mapping declarative and avoids per-call translation. |

---

**End of plan.** Update this document as decisions evolve. When starting a new Claude session, share this file to restore full context.

# Project History — completed phases

Append-only record of the deliverables that landed in each completed phase. The
live `PROJECT_PLAN.md` §8 keeps a one-line summary per finished phase with the
merge-commit SHA; the full per-item checklist is preserved here for archeology.

When a phase finishes, move its full sub-bullets here and replace them in
`PROJECT_PLAN.md` with a single ✅ line.

---

## Phase 1 — Foundation (week 1) — shipped 2026-05-04, PR #1 (`02102c6`)
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

---

## Phase 2 — Value Objects & Enums (week 1-2)
**Goal:** All foundational types in place. Split into two PRs to keep the review surface manageable.

### Phase 2A — value objects + unit enums — shipped 2026-05-04, PR #2 (`81ccaf7`)
- [x] Unit enums: `UnitSystem`, `WeightUnit`, `DimensionUnit`
- [x] Format-only VOs: `CountryCode` (ISO 3166-1 alpha-2), `CurrencyCode` (ISO 4217)
- [x] Measurement VOs: `Weight`, `Dimensions`, `Money`
- [x] Identity VOs: `EmailAddress`, `PhoneNumber`, `AccountNumber`, `PostalCode`, `ServiceAreaCode`, `HsCode`
- [x] PHPStan-level-8 clean on src/, level-6 clean on tests/

### Phase 2B — DHL business enums — shipped 2026-05-05, PR #3 (`d0b1c55`)
Driven by `dhl_reference.pdf` + OpenAPI inline enums.

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

---

## Phase 2C — Reference workbook reconciliation (week 2) — shipped 2026-05-05, PR #5 (`b066283`)
**Goal:** Realign the Phase 2B enums with the new authoritative source (`dhl_reference_data.xlsx`) and unblock the small set of deferred enums that now have a canonical list. **No new domain functionality** — this phase is type-system maintenance.

### 2C.1 — Reconcile shipped enums vs xlsx
For each enum below, diff the cases against the matching xlsx sheet and ship a focused commit. Each diff is its own commit so the history shows exactly what the xlsx changed.

- [x] `CustomsDocumentTypeCode` ↔ `documentTypeCode` sheet (54 → 55: +APP, EDC, FSP, IMP, MFD, PPY; −ATR, CHD, CHP, CP2, HLC)
- [x] `InvoiceReferenceTypeCode` ↔ `invoiceReferenceType` sheet (41 → 19; PDF source carried codes that actually live at other reference levels; pruned to xlsx canon and added INB, SME, USM)
- [x] `LineItemReferenceTypeCode` ↔ `invoiceItemReferenceType` sheet (43 → 41; pruned AAM, INB)
- [x] `PackageReferenceTypeCode` ↔ `customerPackageReferenceType` sheet (14 → 81 unique typeCodes; per-country applicability is a builder/runtime concern, not encoded here)
- [x] `RegistrationNumberTypeCode` ↔ `registrationNumberTypeCode` sheet (29 → 25: +DUT, SUB; −IE, INN, KPP, MRN, OGR, OKP)
- [x] `PackageTypeCode` ↔ `packageTypeCode` sheet (18 → 22: +BB1, BB2, BB3, BB6 — bottle-box variants)
- [x] `DangerousGoodsContentId` ↔ `dangerousGoods` sheet (20 → 23 contentIds: +977, 978 sodium-ion, +YN1 tail-lift truck) and `DangerousGoodsServiceCode` (13 → 16: +HA, HB, YN)

### 2C.2 — Ship newly-unlocked enums
The xlsx provides the authoritative list these were previously waiting on.

- [x] `ProductCode` (36 cases from `productCode` sheet)
- [x] `TrackingEventCode` (65 cases from `trackingEventCode` sheet) — backs the Phase 3 TrackingApi response DTOs
- [x] `LanguageCode` (46 unique 3-letter codes from `languageCode` sheet)
- [x] `UnitOfMeasurement` (59 codes from `unitOfMeasurement` sheet) — distinct from `WeightUnit`/`DimensionUnit`; covers customs line-item quantity units (DOZ, M3, PCS, …)

### 2C.3 — Defer to the phase that actually consumes them
Avoid front-loading. Ship these alongside the DTOs that use them, so we have a concrete consumer to size the enum against.

- `ServiceCode` (~384 codes, grouped by `serviceGroupCode`) → **Phase 4 Shipment** (used in `ValueAddedService` payloads). Decide at that point whether to ship as one large enum or split per `serviceGroupCode` (W=Customs, H=DG, U=Temperature, etc.).
- `OutputImageTemplate` (~66 templates from `outputImageTemplate` sheet) → **Phase 4** (label generation `OutputImageProperties`).
- `CommodityCategory` (~108 codes from `commodityCategory` sheet) → **Phase 4** (export declarations).
- `ShipmentReferenceTypeCode` (~63 codes from `customerShipmentReferenceType` sheet) → **Phase 4** if `CreateShipmentBuilder` needs it; otherwise leave as free string.
- `DhlErrorCode` → still on demand, but the canonical list (`returnStatusMessage`, ~780 codes) now exists. Curated subset added when first caller wants to branch on a specific code (`9001`, `7012`, `422`, …).

### 2C.4 — No new code for these — data, not types
- `country` (235 rows) — already covered by ISO 3166 + format-only `CountryCode` validation. Maintaining a country list is its own engineering problem (see Phase 2A decision).
- `countryPostalcodeFormat` (161 rows) — could enrich `PostalCode` with country-aware regex, but Phase 2A decision is format-only validation. If we ever revisit, this is the source.

---

## Phase 3 — Read-Only APIs (week 2-3)
**Goal:** All GET-style operations working. Sliced into ~4 sub-phases by complexity to keep PRs reviewable.

### Phase 3a — Tracking + Identifier (smallest delta) — shipped 2026-05-05, PR #6 (`46f0a9c`)
- [x] `TrackingApi::getMany()` for `GET /tracking` (multi-shipment, up to 200 numbers per call)
- [x] `IdentifierApi::allocate()` for `GET /identifiers` (SID / PID / HUID / ASID3..24)
- [x] `IdentifierType` enum (7 codes from OpenAPI inline enum)
- [x] `RequestBuilder` upgrade: list-valued query params expand into repeated `?key=v1&key=v2` pairs

### Phase 3b — Address + Products + ReferenceData (simple GETs) — shipped 2026-05-05, PR #7 (`b4d3ee4`)
- [x] `AddressApi::validate()` for `GET /address-validate` (with `AddressValidationType` enum, `ValidatedAddress` + `ServiceArea` DTOs)
- [x] `ProductsApi::list()` for `GET /products` (minimal product DTO; breakdown / VAS tables deferred to Phase 3d alongside the rating consumers)
- [x] `ReferenceDataApi::lookup()` for `GET /reference-data` (with `ReferenceDataset` and `ComparisonOperator` enums)

### Phase 3c — ServicePoint + Epod (medium) — shipped 2026-05-08, PR #11 (`13fe7fa`)
- [x] `ServicePointApi::find()` for `GET /servicepoints` (subset DTO: facility id, address, geo, opening hours; reuses existing `ServicePointType` and `DayOfWeek` enums via `tryFrom` with raw-string fallback for unknown wire values)
- [x] `EpodApi::get()` for `GET /shipments/{id}/proof-of-delivery` (JSON response with base64-encoded documents; `EpodContent` enum covers the 7 content variants)

### Phase 3d — Rates + LandedCost (complex) — shipped 2026-05-08, PR #12 (`1a1c56b`)
- [x] `RatesApi::quote()` for `GET /rates` (single-piece, query-string) and `RatesApi::quoteMany()` for `POST /rates` (multi-piece, JSON body via `RateRequest` DTO; the OpenAPI spec calls the multi-piece operation `exp-api-rates-many`, hence the helper name — there is no separate `/rates-many` endpoint)
- [x] `LandedCostApi::estimate()` for `POST /landed-cost`. Response shape (`supermodelIoLogisticsExpressRates`) is shared with `/rates`, so a single `RatesResponseHydrator` covers all three. Per-line-item charge breakdowns surface as `QuotedProduct::$rawItems` — typed children can fan out when a concrete consumer surfaces.
- [x] `RateRequestBuilder` and `LandedCostRequestBuilder` — first builders in the project; both accumulate cross-field errors and throw a single `InvalidRequestException` (per `PROJECT_PLAN.md` §7).

### Phase 3 cross-cutting
- [x] DTOs for all responses (per sub-phase) — Tracking, Identifier, Address, Products, ReferenceData, Epod, ServicePoint all shipped with typed response DTOs
- [x] Unit tests per API (mocked HTTP) — every shipped `*Api` has a sibling `*ApiTest` under `tests/Unit/Api/` exercising hydration + query-parameter emission against `php-http/mock-client`
- [x] Integration tests against sandbox — covers Tracking (single + multi), Address, ReferenceData, Products, Identifier, Epod (404 path), ServicePoint (Brussels + enum drift detection). Shared `IntegrationTestCase` base with env-gated `makeClient()` / `requireAccountNumber()` helpers and `nextBusinessDay()` for date-stable lookups.

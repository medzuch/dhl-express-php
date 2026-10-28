# Design

## Context

See proposal.md (Why). Current state:
- Hydration is spread across `*ResponseHydrator` classes (Rates, Create Shipment, Pickup, Get Image) and inline code in some `*Api` classes (`TrackingApi::hydrateEvents`). All of them use `Support\HydrationHelper` for typed field extraction.
- `HttpTransport::send()` returns the decoded body array. Response headers reach only `DhlErrorMapper` for error responses (`Retry-After` is already read there).
- `QuotedProduct` exposes `?array $pickupCapabilities` and `list<array> $rawItems`, and `BreakdownDetail::$priceBreakdown` is `list<array>`. All three break CLAUDE.md's "never raw arrays".
- Tracking has two DTOs (`TrackingResponse`, `ShipmentEvent`) with 4 fields each.

## Goals / Non-Goals

**Goals:**
- Every 3.4.0 response field listed in the specs is reachable through a typed property.
- Hydration stays tolerant: absent optional fields become `null` or `[]`, never exceptions.
- No public `array<string, mixed>` remains on response DTOs.

**Non-Goals:**
- Service point marketing fields (`promotion`, `partner.promotion`, `translations`, `mapCulture`, `mapLanguage`, `liteMode`, HTML snippets).
- Exposing `Invocation-Id` / `Message-Reference` on *successful* responses. That would need a response envelope across every `*Api` method, so it is deferred.
- The request-side leftovers from the gap audit (`bookingRequestorDetails`, `Message-Reference-Date`, per-call `Accept-Language`).

## Decisions

### D1. One hydrator class per response root; nested DTOs get static-free factory helpers
Tracking hydration moves out of `TrackingApi` into a `TrackingResponseHydrator`, matching the pattern already used for Rates and Create Shipment. CLAUDE.md forbids static methods, so nested DTOs are built by private methods on the hydrator, not `fromArray()` statics.

### D2. Shared value DTOs for repeated shapes
Several shapes repeat across the tracking, shipment and service point responses:
- **`ServiceArea`** (code, description, facilityCode, inboundSortCode, outboundSortCode, all optional apart from code) is used by tracking shipper/receiver/events and by Create Shipment origin/destination.
- **`Dimensions`** (length, width, height) is used by tracking pieces.
- **`Measurement`** (value, uom) is used by service point limitations.

Reusing them keeps the class count manageable. The alternative, one DTO per JSON location, mirrors the spec more literally but would add about 15 near-duplicate classes.

### D3. Tracking DTO shape
- `TrackingResponse` gains the shipment fields.
- `ShipmentEvent` gains `?string $gmtOffset`, `list<ServiceArea> $serviceAreas` and `?string $signedBy`.
- The new `TrackedPiece` reuses `ShipmentEvent` for its events.
- `TrackingParty` (name, postal address, service areas) covers shipper and receiver.
- `shipperReferences` reuse the existing `ShipmentReferenceTypeCode` with a raw-string fallback, following the `RateNetworkTypeCode` + `rawNetworkTypeCode` pattern, so unknown codes never break hydration. This matters in practice: `adopt-mydhl-api-3-4-0` removes 47 shipment and 41 package reference codes that DHL retired in the 2026-10-25 workbook, and tracking responses for older shipments can still contain them. The same `tryFrom()` + raw-string rule applies to every response field backed by a workbook enum (package type codes and service codes in shipment details and products). The `TrackingResponseHydrator` tests include a fixture with a retired reference code (`AAO`).

### D3a. New DHL warnings surface through `warnings`
The 3.4.0 reference data adds rating warnings 6994–6996 and Create Shipment warnings 7978 and 7980–7984. They arrive in the `warnings` arrays that this change hydrates (see the `shipment-creation` warnings requirement), so they need no dedicated handling. Warnings are exposed as DHL's strings, without parsing codes out of them.

### D4. Typed replacements for the raw arrays (BREAKING)
- `QuotedProduct::$pickupCapabilities` → `?PickupCapability`.
- `$rawItems` → `list<LandedCostItem>`.
- `BreakdownDetail::$priceBreakdown` → `list<PriceBreakdownDetail>`.

`LandedCost\LineItem::$goodsCharacteristics` / `$additionalQuantityDefinitions` are request-side raw arrays. They are fixed here too, so the rule holds library-wide.

`Product` (from `GET /products`) moves to the same capability DTOs as `QuotedProduct`. `Product::$networkTypeCode` is aligned to the enum + raw-string pattern.

### D5. `InvoiceApi::uploadInvoiceData()` returns `UploadInvoiceDataResponse`
The DTO has `string $status` and `list<string> $warnings`. The per-shipment `ShipmentApi::uploadInvoiceData()` stays `void`, because its 200 has no body.

### D6. `Invocation-Id` on exceptions
`DhlApiException` gains `public readonly ?string $invocationId`, as a new trailing constructor parameter so existing subclass construction keeps working. `DhlErrorMapper` reads `getHeaderLine('Invocation-Id')`; PSR-7 header lookup is case-insensitive, and an empty value maps to `null`.

### D7. Fixtures
Each hydrator gets a "full" fixture built from the 3.4.0 spec response examples, extended by hand where the examples omit fields, and a "minimal" fixture. Fixtures live under `tests/Fixtures/<Domain>/`.

## Risks / Trade-offs

- [The class count grows noticeably (roughly 25 new DTOs)] → Shared shapes (D2), and DTOs are grouped by domain directory.
- [Spec examples may not reflect real responses (types as strings vs numbers)] → Hydrators use the tolerant `HydrationHelper` accessors already in place, and the integration tests assert hydration against the live sandbox for tracking, create shipment and rates.
- [The BREAKING type changes on `QuotedProduct` / `BreakdownDetail` / `InvoiceApi`] → Ship together in one minor release with CHANGELOG migration notes. They are rarely used fields.

## Migration Plan

Release as the next minor after 0.4.0, or fold it into 0.4.0 if both are ready together. CHANGELOG migration notes cover the three changed types and `InvoiceApi::uploadInvoiceData()`.

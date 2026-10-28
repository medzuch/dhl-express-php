# Design

## Context

See proposal.md (Why). The current state that shapes the approach:

- `ExportDeclaration` is one DTO shared by three payloads: Create Shipment (`content.exportDeclaration`, single), and the two invoice-data uploads (`content.exportDeclaration[]`, list). In 3.4.0 all three schemas require `lineItems`, `invoice` and `incoterm` on each declaration. The DTO has no `incoterm` and an optional `invoice`, so the invoice-data uploads already send non-conforming payloads today.
- The two 3.4.0 declaration schemas are different, and both set `additionalProperties: false`. Create Shipment uses `supermodelIoLogisticsExpressShipmentExportDeclaration`; the uploads use `supermodelIoLogisticsExpressExportDeclaration`. Validating a built payload against the spec showed that the library always sends `invoice.function`, which the Create Shipment schema rejects (and has rejected since 3.2.2).
- `CreateShipmentBuilder` holds a single `?ExportDeclaration`. It enforces customs rules at `build()` (declarable ⇒ declaration, DDP ⇒ `DutiesTaxes` account, declared value = Σ price × quantity) and reports every violation in one `InvalidRequestException`.
- `Content` requires `Incoterm $incoterm`. `ParentShipment` and `OnDemandDelivery` carry fields 3.4.0 deleted. `LineItemWeight` allows both values to be null.
- `ClientConfig::$xVersion` defaults to `'3.2.0'`.
- Enums are generated from `dhl_reference_data.xlsx` (3.2.2 era). DHL has since published `ExpressReferenceData_2026-10-25.xlsx` and the 3.4.0 reference data guide. These add codes (service codes, `FSR`, `AKO`, `MF`, `SRV`, `A08`/`H8`) and retire many reference, package and document type codes. The 3.4.0 OpenAPI enums match the new workbook, apart from `CE1` and `AAJ`.

## Goals / Non-Goals

**Goals:**
- Every payload the library builds validates against the 3.4.0 schema.
- One DTO model, with no version switches.
- Callers migrating from 0.3.x get compile-time or `build()`-time errors, never silent wire changes.

**Non-Goals:**
- Supporting 3.2.x payloads. A caller can still override `xVersion`, but payloads are 3.4.0-shaped regardless.
- Encrypted PII document images (change `encrypted-document-images`).
- Response-side gaps unrelated to 3.4.0 (change `complete-response-hydration`).
- Enforcing DHL's account-specific limit of 5 export declarations.

## Decisions

### D1. Keep one `ExportDeclaration` DTO and make it stricter; the destination chooses the wire shape
`incoterm` (`?Incoterm`) is added and `invoice` becomes a required constructor argument (non-nullable `ExportInvoice`).

The two declaration schemas differ as follows:

| | Create Shipment | Invoice-data uploads |
|---|---|---|
| `invoice.function` | not allowed | required |
| invoice `signatureName`, `signatureTitle`, `signatureImage`, `instructions`, `customerDataTextEntries`, `totalNetWeight`, `totalGrossWeight`, `termsOfPayment` | allowed | not allowed |
| declaration `exportReason`, `declarationNotes`, `packageMarks`, `payerVATNumber` | allowed | not allowed |
| line item `additionalInformation` | allowed | not allowed |

One set of DTOs is kept, and the destination decides the shape:
- `ExportInvoice::$function` becomes `?InvoiceFunction` (default `null`). `toArray()` produces the Create Shipment shape and never emits `function`.
- `ExportInvoice::toInvoiceDataArray()` and `ExportDeclaration::toInvoiceDataArray()` produce the upload shape, including `function`.
- `ExportDeclaration::invoiceDataUploadErrors()` lists the field paths that make a declaration invalid for an upload: a missing `incoterm`, a missing `invoice.function`, or any Create-Shipment-only field that is set. The two upload request constructors throw `InvalidArgumentException` naming `exportDeclarations[i].<field>`. Fields are never silently dropped.

*Alternative considered:* separate DTOs for the uploads (`InvoiceDataExportDeclaration` with its own invoice and line item). The types would be stricter, but this adds about three near-duplicate classes and a larger migration for callers.

`incoterm` is nullable on the DTO only so the builder can fill it from the shipment-level default (D3). The invoice-data request DTOs have no default to inherit from, so their constructors check that every declaration has a non-null `incoterm` and throw `InvalidArgumentException` otherwise.

*Alternative considered:* making `incoterm` non-nullable on the DTO. That forces callers to repeat the shipment incoterm on every declaration, and leaves the shipment-level incoterm with nothing to do.

### D2. Builder: `addExportDeclaration()` replaces `withExportDeclaration()`
`CreateShipmentBuilder` stores `list<ExportDeclaration>`. The new `addExportDeclaration(ExportDeclaration)` appends. `withExportDeclaration()` is removed rather than kept as an alias, because its "set exactly one" meaning would be misleading next to a list. This matches the builder's existing `with*`/accumulate naming: `withPackage` already appends, but a new verb makes the multiplicity explicit in the 0.4.0 migration notes.

`Content::$exportDeclaration` becomes `Content::$exportDeclarations` (`list<ExportDeclaration>`), serialized as `exportDeclarations` only when non-empty.

### D3. Shipment-level incoterm becomes an optional default
`Content::$incoterm` becomes `?Incoterm`. At `build()`, each declaration with `incoterm === null` is rebuilt with the shipment-level incoterm. The DTO is `readonly`, so this happens through a public `withIncoterm()` copy method on `ExportDeclaration`. (It was planned as private, but the builder is a separate class and cannot call a private method. An immutable copy method is harmless public API.) If both are null, the builder reports `content.exportDeclarations[i].incoterm`.

The DDP rule checks the shipment-level incoterm and every resolved declaration incoterm. The declared-value check sums line items across all declarations.

### D4. Validation of new constraints lives in the DTOs
The new constraints are enforced in constructors with `InvalidArgumentException`, which is how existing DTOs self-validate:
- `LineItemWeight`: at least one value.
- `DangerousGoods`: `packagesCount` 1–999, `customDescription` ≤ 1000.
- `ExportDeclaration`: `exportReason` ≤ 30, `declarationNotes` ≤ 3.
- `ExportInvoice`: `signatureName`/`signatureTitle` ≤ 35, `instructions` ≤ 1 × 300, `customerDataTextEntries` ≤ 6 × 45.
- `ExportLineItem`: `additionalInformation` ≤ 8.

The builder only enforces cross-field rules.

### D5. Separate dimension unit, reusing `UnitSystem`
`Content` gains `?UnitSystem $dimensionUnitOfMeasurement`. The builder's per-package unit check compares dimensions against `dimensionUnitOfMeasurement ?? unitOfMeasurement` and weight against `unitOfMeasurement`. `DangerousGoods::$dryIceUnitOfMeasurement` also reuses `UnitSystem`.

### D6. `PickupApi::cancel()` returns `CancelPickupResponse`
This is a new `final readonly` DTO with `?string $status`, `?string $title`, `?string $detail`, `?string $instance` and `?string $message`. The 200 body reuses `supermodelIoLogisticsExpressErrorResponse`, and the 3.4.0 examples put the outcome text in `message` (e.g. "Successfully cancelled.", "Booking already cancelled or completed."), so `message` is exposed as well. The hydrator tolerates an empty body (3.2.x-style servers, sandbox quirks). Fields stay strings rather than an enum: DHL documents the three outcomes only in prose, with no code list in the workbook or the spec.

### D7. New create flag as a parameter, matching existing flags
`ShipmentApi::create()` gains `?bool $bypassLabelfreeError = null`, following `strictValidation` / `bypassPLTError`. A parameter object was considered, but it would break every caller for one flag. It can come later if more flags arrive.

### D8. Regenerate code enums from the 2026-10-25 workbook and remove retired codes
`ExpressReferenceData_2026-10-25.xlsx` replaces `dhl_reference_data.xlsx` as the authoritative workbook, and the precedence rule in CLAUDE.md is unchanged. Every enum backed by a workbook sheet is regenerated to match its sheet exactly. Added cases keep the existing naming conventions, with descriptive names for digit-prefixed codes such as `5A`.

Retired codes are **removed**, not deprecated. Three sources agree that they are gone: the 3.4.0 OpenAPI enums, the PDF's new "Shipment/Piece reference type" sections (guide 3.3.0), and the live API's error 7021. Keeping them would let callers build requests that are certain to fail.

*Alternative considered:* keeping the cases with `@deprecated`. This was rejected because PHP enums cannot emit runtime deprecation notices, so the cases would fail silently at DHL rather than at compile or analysis time.

**Majority rule for disagreements.** If two of the three current sources list a code, it is kept, with a docblock naming the sources:
- `CE1` is in the spec and the PDF but not the workbook.
- Package reference `AAJ` is in the workbook and the PDF but not the spec.

**Response-side tolerance.** Retired codes can still appear in responses, such as tracking `shipperReferences` for older shipments. Hydrators therefore never call `::from()` on these enums. They use `tryFrom()` plus a raw string. Change `complete-response-hydration` D3 already plans this.

**Not adopted.** Error 7185 references `areMultipleInvoiceDataSetsToBeUploadedLater`, which the 3.4.0 OpenAPI spec does not define. Request shapes come from the spec, so the field is not added.

### D10. Client-side checks for new DHL errors
These checks reject a request before sending only when DHL is certain to reject it:
- **7183:** `ShipmentApi::create()` raises `InvalidArgumentException` when both `bypassLabelfreeError` and `bypassPLTError` are `true`.
- **7188:** `CreateShipmentBuilder` reports duplicate `invoice.number` values across declarations.
- **7191:** `CreateShipmentBuilder` reports a description matching `^[\s|]*$`.

The 7981 and 7978 warnings (bypass ignored without `PZ`; declarations ignored with `PM`) are documented in the README rather than enforced, because DHL treats them as warnings. 7187 (the account-specific declaration limit) is left to DHL, apart from the schema maximum of 100.

### D11. `getManyByPieceId()` takes a list
`getManyByPieceId(string ...$pieceTrackingNumbers)` cannot take `requestGMTOffsetPerEvent`, because PHP allows no parameter after a variadic. It becomes `getManyByPieceId(array $pieceTrackingNumbers, ?TrackingView, ?TrackingLevelOfDetail, ?bool $requestControlledAccessDataCodes, ?bool $requestGMTOffsetPerEvent)`, mirroring `getMany()`. This is BREAKING: callers change `getManyByPieceId('a', 'b')` to `getManyByPieceId(['a', 'b'])`. Dropping piece-ID lookups from the requirement was rejected because it would leave the three multi-tracking operations inconsistent.

### D9. Response `invoiceNumber`
`ShipmentDocument` gains `?string $invoiceNumber`, populated by `CreateShipmentResponseHydrator`. Nothing else in the response changes in this change.

## Risks / Trade-offs

- [The sandbox may still accept or require 3.2.x shapes after the header bump] → Run the Create Shipment integration test against the sandbox with `x-version: 3.4.0` before merging. If the sandbox lags production, record that in the PR and keep the unit tests authoritative.
- [Callers relying on `withExportDeclaration()` get a fatal "undefined method" at upgrade] → Intentional for a 0.x minor bump. Spell out the before/after in the CHANGELOG migration section.
- [Removing retired enum cases breaks callers who reference them] → Intentional. DHL already rejects these codes (7021). The CHANGELOG lists every removed case, and PHPStan flags the references at analysis time.
- [The workbook and the spec could diverge again in a later DHL release] → The majority rule (D8) and the source docblocks make each kept code's justification traceable.
- [Implicit incoterm inheritance could hide caller mistakes] → Only null declaration incoterms are filled. An explicit declaration incoterm always wins, and the README example shows both styles.
- [`incoterm` being nullable on `ExportDeclaration` but required on the wire] → Covered by constructor checks in the invoice-data request DTOs and by builder checks for Create Shipment, each with a unit test.

## Migration Plan

1. Land as one PR on a feature branch and release as **0.4.0**.
2. Add a CHANGELOG "Migrating from 0.3.x" section covering:
   - `withExportDeclaration` → `addExportDeclaration`
   - `incoterm` on declarations
   - removed `ParentShipment` / `OnDemandDelivery` fields
   - `LineItemWeight` non-empty
   - `PickupApi::cancel()` return type
   - the `xVersion` default
   - removed enum cases, listed per enum
   - the new pre-send checks (7183, 7188, 7191)
3. Rollback: callers pin `^0.3`. There is no server-side state.

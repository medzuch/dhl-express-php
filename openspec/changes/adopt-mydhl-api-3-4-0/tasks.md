# Tasks

All implementation tasks follow TDD per CLAUDE.md: write the failing test first, then the code. "Verify" means the named test passes and `make check` stays green.

## 1. Version switch and reference codes

- [x] 1.1 Change the `ClientConfig::$xVersion` default to `'3.4.0'`, updating `ClientConfigTest` and `RequestBuilderTest` first; verify both assert `x-version: 3.4.0`
- [x] 1.2 Point the CLAUDE.md "Read first" list (and README references) at `ExpressReferenceData_2026-10-25.xlsx`, `dpdhl-express-api-3.4.0_9.yaml` and `MyDHL API Reference data guide (3.4.0).pdf`; label `dhl_reference_data.xlsx`, `dhl_openapi.yaml` and `dhl_reference.pdf` as historical (kept, not deleted); verify with `grep -rn "dhl_reference_data.xlsx\|dhl_openapi.yaml\|dhl_reference.pdf" CLAUDE.md README.md src`, which returns only "historical" mentions
- [x] 1.3 Add a reproducible enum-vs-workbook check script under `bin/` or `tools/` (dev only, not shipped in the package) that reports codes missing from or extra in each workbook-backed enum; verify that running it on the current tree lists the differences this change resolves
- [x] 1.4 Regenerate the reference-type enums from the new workbook: `ShipmentReferenceTypeCode` (16), `PackageReferenceTypeCode` (40 + `AAJ` kept under the majority rule, with its source docblock), `InvoiceReferenceTypeCode` (+`AKO`), `LineItemReferenceTypeCode` (+`AKO`, `MF`, `SRV`) and `RegistrationNumberTypeCode` (+`FSR`), removing retired cases; update the enum unit tests first; verify the check script reports no differences for these enums
- [x] 1.5 Regenerate `PackageTypeCode` (remove `WB1`/`WB2`/`WB3`/`WB6`; keep `CE1` with a majority-rule docblock) and `CustomsDocumentTypeCode` (remove `CHA`, `POA`); verify with enum tests and the check script
- [x] 1.6 Regenerate `ServiceCode` (+84 including `YL`; remove `U7`/`U8`/`U9`), `DangerousGoodsContentId` (+`A08`) and `DangerousGoodsServiceCode` (+`H8`), keeping the case-naming conventions for digit-prefixed codes; verify with enum tests and the check script
- [x] 1.7 Update enum docblocks that cite `dhl_reference_data.xlsx` to cite the new workbook; fix every library and test usage of removed cases; verify with `make analyse-all` (no references to removed cases) and `grep -rn "dhl_reference_data.xlsx" src` returning nothing

## 2. Customs declaration model

- [x] 2.1 `LineItemWeight`: reject construction with neither net nor gross value; verify with new `LineItemWeightTest` cases (net only, gross only, both, neither → exception)
- [x] 2.2 `ExportLineItem`: add `additionalInformation` (list<string>, ≤ 8) with serialization; verify with `ExportLineItemTest`
- [x] 2.3 `ExportInvoice`: add `signatureName`, `signatureTitle` (≤ 35), `signatureImage`, `instructions` (≤ 1 × 300), `customerDataTextEntries` (≤ 6 × 45), `totalNetWeight`, `totalGrossWeight` and `termsOfPayment`, all optional with length checks; verify with `ExportInvoiceTest` (serialization, omission when unset, each limit)
- [x] 2.4 `ExportDeclaration`: add `?Incoterm $incoterm`, make `invoice` required, and add `exportReason` (≤ 30), `declarationNotes` (≤ 3), `packageMarks` and `payerVATNumber`; add a public `withIncoterm()` copy method used for incoterm defaulting; confirm no `licenses`/`exportReference`/`destinationPortName` keys exist; verify with an updated `ExportDeclarationTest`
- [x] 2.5 `UploadInvoiceDataRequest` and `UploadStandaloneInvoiceDataRequest`: reject declarations without an incoterm; verify with new constructor tests and updated `InvoiceApiTest` / `ShipmentApiUploadGetTest` payload assertions showing `incoterm` on the wire
- [x] 2.6 Split the declaration wire shape per D1: make `ExportInvoice::$function` optional and stop emitting it from `toArray()`; add `toInvoiceDataArray()` on `ExportInvoice` / `ExportDeclaration`; add `ExportDeclaration::invoiceDataUploadErrors()` and use it in both upload request constructors (missing incoterm, missing `function`, Create-Shipment-only fields); verify with DTO tests, upload constructor tests per field, and payloads from both upload requests validated against `supermodelIoLogisticsExpressExportDeclaration`

## 3. Create Shipment request

- [x] 3.1 `Content`: replace `exportDeclaration` with `exportDeclarations` (list), make `incoterm` nullable, and add `?UnitSystem $dimensionUnitOfMeasurement`; verify with `ContentTest` serialization cases (no `exportDeclaration` key, omitted when empty, dimension unit emitted)
- [x] 3.2 `CreateShipmentBuilder`: replace `withExportDeclaration` with `addExportDeclaration`; enforce declarable ⇒ ≥ 1 declaration and ≤ 100 declarations; verify with `CreateShipmentBuilderCustomsValidationTest` cases for 0, 1, 3 and 101 declarations
- [x] 3.3 `CreateShipmentBuilder`: implement incoterm defaulting (D3), the DDP rule across all incoterms, and the declared-value sum across declarations; verify with test cases for inherited, explicit and missing incoterm, DDP on a declaration only, and a two-declaration sum
- [x] 3.4 `CreateShipmentBuilder`: validate package dimensions against `dimensionUnitOfMeasurement` when set (`withDimensionUnitSystem()`); verify with a mixed-unit builder test
- [x] 3.5 `DangerousGoods`: add `dryIceUnitOfMeasurement` and `packagesCount` (1–999), and raise the `customDescription` limit to 1000; verify with `DangerousGoodsTest` boundary cases (0, 1, 999, 1000; 1000/1001 chars)
- [x] 3.6 `ParentShipment`: remove `typeCode`, `number` and `issuerCountryCode`; verify `ParentShipmentTest` asserts the exact `{productCode, packagesCount}` payload
- [x] 3.7 `OnDemandDelivery`: remove `requestedDeliveryDate`; verify `OnDemandDeliveryTest` asserts the key is absent
- [x] 3.8 `ShipmentApi::create()`: add the `?bool $bypassLabelfreeError` parameter and reject `bypassLabelfreeError=true` together with `bypassPLTError=true` before sending (7183); verify with `ShipmentApiTest` URL assertions for set and unset, and a test that both-true throws and makes no HTTP call
- [x] 3.9 `CreateShipmentBuilder`: report duplicate invoice numbers across declarations (7188) and descriptions matching `^[\s|]*$` (7191); verify with builder tests for a duplicate pair, pipes only, whitespace only, and a valid description containing `|`
- [x] 3.10 Add a golden Create Shipment request fixture (`tests/Fixtures/shipments/create-shipment-request-3.4.0.json`) built from a customs shipment that uses the 3.4.0 features, plus a test comparing the builder output with it (the repo had no request fixtures to update); verify the fixture validates against `supermodelIoLogisticsExpressCreateShipmentRequest` in the 3.4.0 spec and the test passes

## 4. Create Shipment response

- [x] 4.1 `ShipmentDocument`: add `?string $invoiceNumber` and hydrate it in `CreateShipmentResponseHydrator`; verify with a hydrator test using a fixture that has and lacks `invoiceNumber`

## 5. Pickups and tracking

- [x] 5.1 Add the `CancelPickupResponse` DTO and hydrator, and change `PickupApi::cancel()` to return it; verify with `PickupApiTest` cases for a 200 with a body, a 200 with an empty body, and a 404 → not-found exception
- [x] 5.2 Add the optional `requestGMTOffsetPerEvent` to `TrackingApi::getMany`, `getManyByPieceId` and `getManyByReference`, changing `getManyByPieceId` from variadic to `list<string>` with `getMany`'s view options (D11); verify with `TrackingApiTest` URL assertions for set and unset

## 6. Documentation and release

- [x] 6.1 Update the README Create Shipment examples to `addExportDeclaration`, per-declaration incoterm and the signed invoice; verify each README snippet against the current API (php -l on extracted snippets or a doc test)
- [x] 6.2 Add a CHANGELOG `[0.4.0]` entry with Added/Changed/Removed and a "Migrating from 0.3.x" section covering every **BREAKING** item in proposal.md, including the removed enum cases listed per enum; verify every BREAKING bullet has a migration line
- [x] 6.3 Document in the README the DHL warnings the library does not enforce: 7981 (`bypassLabelfreeError` is ignored without the `PZ` service) and 7978 (export declarations are ignored with the `PM` service); verify the README section exists

## 7. Integration

- [x] 7.1 Run `make check`; verify tests, PHPStan (src at level max, tests at 6) and cs-check all pass with ≥ 90% line coverage on `src/`
- [x] 7.2 Run `make test-integration` with `DHL_API_KEY` set against the sandbox; verify Create Shipment (customs-declarable, one declaration), Upload Invoice Data and Cancel Pickup succeed with `x-version: 3.4.0`, or record any sandbox-lag findings in the PR
  - Sandbox findings (2026-10-28, `x-version: 3.4.0`): the integration suite ran 21 tests, with 19 passing.
    - Both tracking tests got HTTP 500 "999: Process failure". The same call fails identically with `x-version: 3.2.0`, so this is a sandbox outage, not this change.
    - Create Shipment (customs-declarable, one declaration, `validateDataOnly`) got business error 8009: the account is not import-enabled. Because this is not a 422 schema error, the 3.4.0 payload passed DHL's schema validation.
    - Upload Invoice Data got 7115: this username is not allowed to upload invoice data. That is an account permission and needs DHL to enable it. Both upload payloads validate against the 3.4.0 schemas in unit tests.
    - Create + Cancel Pickup succeeded, and `cancel()` returned `message: "Successfully cancelled"` with the `instance` path.
    - The existing pickup integration tests omit package dimensions. The sandbox rejects that with 422, and the tests swallow the error. This is a pre-existing gap, out of scope here.

## Workflow follow-up

- Merge via PR, tag 0.4.0, then start `encrypted-document-images` and `complete-response-hydration`.
- Archive this change after release.

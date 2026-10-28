# Proposal

## Why

DHL published MyDHL API 3.4.0 (`specs/dhl/dpdhl-express-api-3.4.0_9.yaml`, released 2026-10-25). Its Create Shipment payload is structurally incompatible with 3.2.x: a single `exportDeclaration` became an `exportDeclarations[]` list, several fields were removed, and `content`, `parentShipment` and `onDemandDelivery` are now `additionalProperties: false`, so payloads built for 3.2.x are rejected. The library still sends `x-version: 3.2.0`. DHL also published matching reference data: `ExpressReferenceData_2026-10-25.xlsx` and `MyDHL API Reference data guide (3.4.0).pdf`. That data adds codes and retires many others, and the live API now rejects retired reference codes (error 7021). We do a hard switch to 3.4.0 now, while still on 0.x, instead of maintaining two wire formats.

## What Changes

- **BREAKING** Default `x-version` header becomes `3.4.0`; 3.2.x payload shapes are no longer produced.
- **BREAKING** Create Shipment sends `content.exportDeclarations[]` (1–100 entries, DHL default limit 5) instead of `content.exportDeclaration`. The builder accepts multiple export declarations.
- **BREAKING** Every export declaration carries a required `incoterm` and `invoice`. This also fixes an existing defect: the invoice-data upload endpoints already required `incoterm` and the library never sent it.
- `content.incoterm` becomes optional on Create Shipment; the shipment-level incoterm is used as the default for declarations that do not set their own.
- **BREAKING** Removed from the wire model because 3.4.0 dropped them: `parentShipment.typeCode/number/issuerCountryCode`, `onDemandDelivery.requestedDeliveryDate`.
- **BREAKING** A line-item weight must carry at least one of `netValue` / `grossValue`.
- Create Shipment no longer sends `invoice.function`, which its schema rejects (a defect dating from 3.2.2). `ExportInvoice::$function` becomes optional. It is still required for the invoice-data uploads, and those uploads now reject the declaration, invoice and line-item fields that only Create Shipment accepts.
- Export declaration gains the fields 3.4.0 defines but the library never modelled: `exportReason`, `declarationNotes`, `packageMarks`, `payerVATNumber`, line-item `additionalInformation`, and invoice `signatureName`, `signatureTitle`, `signatureImage`, `instructions`, `customerDataTextEntries`, `totalNetWeight`, `totalGrossWeight`, `termsOfPayment`.
- New Create Shipment inputs: `content.dimensionUnitOfMeasurement`, dangerous goods `dryIceUnitOfMeasurement` and `packagesCount`, `customDescription` up to 1000 chars, and the `bypassLabelfreeError` query flag.
- Create Shipment response exposes `documents[].invoiceNumber`.
- Multi-shipment tracking accepts `requestGMTOffsetPerEvent`.
- **BREAKING** `TrackingApi::getManyByPieceId()` takes `list<string>` instead of variadic strings, because PHP allows no parameter after a variadic. It gains the same view options as `getMany()`.
- **BREAKING** Pickup cancellation returns a typed result (cancelled / already cancelled / already completed) instead of `void`.
- Code enums are regenerated from `ExpressReferenceData_2026-10-25.xlsx`, which becomes the authoritative workbook:
  - Added: 84 service codes (temperature-controlled, pharma, GoGreen Plus, `YL`, …), dangerous goods `A08` / `H8`, registration number type `FSR`, invoice reference `AKO`, line-item references `AKO`, `MF`, `SRV`.
  - **BREAKING** Removed retired codes: 47 shipment reference types, 41 package reference types, package types `WB1`/`WB2`/`WB3`/`WB6`, customs document types `CHA`/`POA`, and service codes `U7`/`U8`/`U9`.
  - `CE1` and `AAJ` are kept where two of the three DHL sources still list them.
- Client-side checks for new DHL errors:
  - `bypassLabelfreeError` combined with `bypassPLTError` (7183).
  - Duplicate invoice numbers across export declarations (7188).
  - A content description made only of pipes or whitespace (7191).
- The primary references in CLAUDE.md/README move to the 3.4.0 OpenAPI spec, the 2026-10-25 workbook and the 3.4.0 PDF guide. The 3.2.2-era files stay in `specs/dhl/`, labelled historical.

## Capabilities

### New Capabilities

- `client-configuration`: API version and other client-wide request settings the library sends to DHL.
- `shipment-creation`: Building and submitting Create Shipment requests and exposing their results.
- `customs-declaration`: Export declaration content (line items, invoice, incoterm, references) shared by Create Shipment and the invoice-data upload endpoints.
- `pickup-management`: Creating, updating and cancelling DHL courier pickups.
- `shipment-tracking`: Retrieving tracking information for one or many shipments.
- `reference-codes`: The set of DHL code values the library accepts for typed fields, and which DHL source they are taken from.

### Modified Capabilities

_None. The project has no baseline specs yet._

## Impact

- **Code:** `ClientConfig`, `Dto/Shipment/{Content, ExportDeclaration, ExportInvoice, ExportLineItem, LineItemWeight, ParentShipment, OnDemandDelivery, DangerousGoods, ShipmentDocument, CreateShipmentResponseHydrator}`, `Dto/Shipment/UploadInvoiceDataRequest`, `Dto/Invoice/UploadStandaloneInvoiceDataRequest`, `Builder/CreateShipmentBuilder`, `Api/{ShipmentApi, PickupApi, TrackingApi}`, `Enum/{ServiceCode, DangerousGoodsContentId, DangerousGoodsServiceCode, RegistrationNumberTypeCode, InvoiceReferenceTypeCode, LineItemReferenceTypeCode, ShipmentReferenceTypeCode, PackageReferenceTypeCode, PackageTypeCode, CustomsDocumentTypeCode}`, plus a new pickup cancellation result DTO. Enum docblocks that cite `dhl_reference_data.xlsx` are updated to cite the new workbook.
- **Tests:** builder, DTO and API unit tests; JSON fixtures under `tests/Fixtures/` regenerated against 3.4.0 examples; the sandbox integration test for Create Shipment.
- **Docs:** README builder examples, CHANGELOG entry for **0.4.0** with a migration section.
- **Callers:** every caller that builds customs-declarable shipments, uses `ParentShipment` / `OnDemandDelivery::requestedDeliveryDate`, references a retired enum case, or calls `PickupApi::cancel()` must update. No new dependencies.
- **Follow-ups:** `encrypted-document-images` and `complete-response-hydration` build on this change and must land after it.

# Spec Delta

## Purpose

Describes the export declaration content (line items, commercial invoice, incoterm, references, notes) that callers supply for customs-declarable shipments, shared by Create Shipment and the invoice-data upload endpoints.

## ADDED Requirements

### Requirement: Export declaration requires line items, invoice and incoterm
Every export declaration sent to DHL SHALL contain at least one line item, an invoice (with number and date) and an incoterm. Line items and invoice SHALL be required when the declaration is constructed. A declaration without its own incoterm SHALL be accepted only where the request supplies a default (see shipment-creation); otherwise the request SHALL be rejected before sending.

#### Scenario: Complete declaration
- **WHEN** a caller creates an export declaration with one line item, an invoice and incoterm `DAP`
- **THEN** the serialized declaration contains `lineItems`, `invoice` and `incoterm: "DAP"`

#### Scenario: Declaration without invoice
- **WHEN** a caller creates an export declaration without an invoice
- **THEN** construction fails

#### Scenario: Missing incoterm on invoice-data upload
- **WHEN** a caller builds an invoice-data upload request containing a declaration without an incoterm
- **THEN** request construction fails with an invalid-argument error and nothing is sent

#### Scenario: Incoterm on invoice-data upload
- **WHEN** a caller uploads invoice data with declarations that all have an incoterm
- **THEN** every serialized `content.exportDeclaration[]` entry contains its `incoterm`

### Requirement: Line item weight carries at least one value
A line item weight SHALL contain a net weight, a gross weight, or both. A weight with neither SHALL be rejected at construction.

#### Scenario: Net weight only
- **WHEN** a line item weight is created with `netValue: 1.5` and no gross value
- **THEN** it serializes as `{"netValue": 1.5}`

#### Scenario: Neither value
- **WHEN** a line item weight is created with no net and no gross value
- **THEN** construction fails with an invalid-argument error

### Requirement: Export declaration supports 3.4.0 descriptive fields
For Create Shipment, an export declaration SHALL accept the optional fields `exportReason` (max 30 chars), `declarationNotes` (up to 3 notes), `packageMarks`, `payerVATNumber`, plus everything previously supported, and SHALL omit unset optional fields from the payload.

#### Scenario: Declaration notes
- **WHEN** a declaration is created with two declaration notes
- **THEN** the payload contains `declarationNotes` with two `{value}` entries

#### Scenario: Unset optional fields
- **WHEN** a declaration is created without `packageMarks`
- **THEN** the payload contains no `packageMarks` key

### Requirement: Line items support additional information
For Create Shipment, a line item SHALL accept an optional list of additional information strings and serialize it as `additionalInformation`.

#### Scenario: Additional information present
- **WHEN** a line item is created with additional information `["Made of cotton"]`
- **THEN** the serialized line item contains `additionalInformation: ["Made of cotton"]`

### Requirement: Invoice supports signature and summary fields
For Create Shipment, the export invoice SHALL accept the optional fields `signatureName`, `signatureTitle`, `signatureImage` (base64), `instructions`, `customerDataTextEntries`, `totalNetWeight`, `totalGrossWeight` and `termsOfPayment`, so that DHL-generated paperless-trade invoices can be signed and fully populated.

#### Scenario: Signed invoice
- **WHEN** an invoice is created with signature name `Jane Doe`, title `Export Manager` and a base64 signature image
- **THEN** the serialized invoice contains `signatureName`, `signatureTitle` and `signatureImage` with those values

### Requirement: Invoice function is sent only to the invoice-data uploads
Create Shipment SHALL NOT send `invoice.function`, because its schema rejects it. The invoice-data uploads SHALL send `invoice.function` and SHALL require it on every declaration.

#### Scenario: Create Shipment invoice
- **WHEN** a shipment is built with a declaration whose invoice has function `export`
- **THEN** the serialized `content.exportDeclarations[0].invoice` has no `function` key

#### Scenario: Invoice-data upload invoice
- **WHEN** invoice data is uploaded with a declaration whose invoice has function `export`
- **THEN** the serialized `content.exportDeclaration[0].invoice.function` is `"export"`

#### Scenario: Invoice-data upload without function
- **WHEN** an invoice-data upload request contains a declaration whose invoice has no function
- **THEN** request construction fails with an invalid-argument error naming `exportDeclarations[0].invoice.function`

### Requirement: Invoice-data uploads reject Create-Shipment-only fields
An invoice-data upload request SHALL be rejected before sending, and SHALL NOT drop fields silently, if a declaration sets any field the upload schema does not define. These are the invoice signature and summary fields, the declaration fields `exportReason`, `declarationNotes`, `packageMarks` and `payerVATNumber`, and line item `additionalInformation`.

#### Scenario: Shipment-only field on an upload
- **WHEN** an invoice-data upload request contains a declaration with `packageMarks` set
- **THEN** request construction fails with an invalid-argument error naming `exportDeclarations[0].packageMarks`

### Requirement: Fields removed in 3.4.0 are not sent
Export declarations SHALL NOT serialize `licenses`, `exportReference` or `destinationPortName`, which MyDHL API 3.4.0 no longer defines.

#### Scenario: Serialized declaration
- **WHEN** any export declaration is serialized
- **THEN** the payload contains none of the keys `licenses`, `exportReference`, `destinationPortName`

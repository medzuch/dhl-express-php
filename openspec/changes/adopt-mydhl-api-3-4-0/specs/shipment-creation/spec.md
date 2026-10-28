# Spec Delta

## Purpose

Covers how callers build and submit DHL Create Shipment requests, the validation applied before sending, and the results returned from a created shipment.

## ADDED Requirements

### Requirement: Create Shipment sends a list of export declarations
Create Shipment SHALL serialize export declarations as `content.exportDeclarations` (a list) and SHALL NOT send `content.exportDeclaration`. Callers SHALL be able to add multiple export declarations to one shipment.

#### Scenario: Single declaration
- **WHEN** a customs-declarable shipment is built with one export declaration
- **THEN** the payload contains `content.exportDeclarations` with exactly one entry and no `content.exportDeclaration` key

#### Scenario: Multiple declarations
- **WHEN** a shipment is built with three export declarations
- **THEN** the payload contains `content.exportDeclarations` with three entries in the order they were added

### Requirement: Export declaration count is bounded
The builder SHALL reject a shipment with more than 100 export declarations. It SHALL NOT enforce DHL's account-level default limit of 5.

#### Scenario: Too many declarations
- **WHEN** a shipment is built with 101 export declarations
- **THEN** `build()` fails with an invalid-request error naming `content.exportDeclarations`

### Requirement: Customs-declarable shipments require an export declaration
When `isCustomsDeclarable` is true, the builder SHALL require at least one export declaration.

#### Scenario: Declarable without declaration
- **WHEN** a shipment with `isCustomsDeclarable: true` is built without any export declaration
- **THEN** `build()` fails with an invalid-request error naming `content.exportDeclarations`

### Requirement: Shipment-level incoterm is optional and acts as a default
`content.incoterm` SHALL be optional. When the builder has a shipment-level incoterm, each export declaration without its own incoterm SHALL inherit it. A declaration with neither its own nor a shipment-level incoterm SHALL be rejected.

#### Scenario: Inherited incoterm
- **WHEN** the builder has incoterm `DAP` and an export declaration without an incoterm
- **THEN** the serialized declaration contains `incoterm: "DAP"` and `content.incoterm` is `"DAP"`

#### Scenario: No incoterm anywhere
- **WHEN** a customs-declarable shipment has no shipment-level incoterm and a declaration without an incoterm
- **THEN** `build()` fails with an invalid-request error naming that declaration's `incoterm`

#### Scenario: Non-declarable without incoterm
- **WHEN** a non-customs-declarable shipment is built without any incoterm
- **THEN** the payload contains no `content.incoterm` key and `build()` succeeds

### Requirement: Cross-field customs rules consider all declarations
The DDP rule (a `DutiesTaxes` account is required) SHALL apply when the shipment-level incoterm or any declaration's incoterm is `DDP`. The declared-value check SHALL compare `declaredValue` with the sum of price × quantity across the line items of all declarations, within ±0.01.

#### Scenario: DDP on a declaration only
- **WHEN** a declaration uses incoterm `DDP`, the shipment-level incoterm is `DAP`, and there is no `DutiesTaxes` account
- **THEN** `build()` fails with an invalid-request error about the missing duties/taxes account

#### Scenario: Declared value across declarations
- **WHEN** two declarations have line item totals of 60.00 and 40.00 and `declaredValue` is 100.00
- **THEN** the declared-value check passes

### Requirement: Dimension unit can differ from weight unit
Create Shipment SHALL accept an optional `content.dimensionUnitOfMeasurement` (`metric` or `imperial`). When it is set, package dimensions SHALL be validated against it instead of the shipment-level unit.

#### Scenario: Imperial dimensions, metric weight
- **WHEN** a shipment uses unit `metric` and dimension unit `imperial`
- **THEN** the payload contains `content.unitOfMeasurement: "metric"` and `content.dimensionUnitOfMeasurement: "imperial"`

### Requirement: Dangerous goods support 3.4.0 fields
A dangerous goods entry SHALL accept an optional `dryIceUnitOfMeasurement` (`metric`/`imperial`), an optional `packagesCount` (integer 1–999), and a `customDescription` of up to 1000 characters.

#### Scenario: Packages count out of range
- **WHEN** a dangerous goods entry is created with `packagesCount: 1000`
- **THEN** construction fails with an invalid-argument error

#### Scenario: Long custom description
- **WHEN** a dangerous goods entry is created with a 900-character custom description
- **THEN** it is accepted and serialized unchanged

### Requirement: Parent shipment carries only product code and packages count
A parent shipment reference SHALL serialize only `productCode` and `packagesCount`.

#### Scenario: Parent shipment payload
- **WHEN** a shipment is built with a parent shipment of product `P` and 3 packages
- **THEN** `parentShipment` is exactly `{"productCode": "P", "packagesCount": 3}`

### Requirement: On-demand delivery has no requested delivery date
On-demand delivery options SHALL NOT include `requestedDeliveryDate`.

#### Scenario: On-demand delivery payload
- **WHEN** a shipment is built with on-demand delivery option `servicepoint`
- **THEN** the `onDemandDelivery` payload has no `requestedDeliveryDate` key

### Requirement: Labelfree validation can be bypassed
Creating a shipment SHALL accept an optional flag that sends the `bypassLabelfreeError` query parameter, alongside the existing `strictValidation`, `bypassPLTError` and `validateDataOnly` flags.

#### Scenario: Bypass flag set
- **WHEN** a shipment is created with bypass-labelfree enabled
- **THEN** the request URL contains `bypassLabelfreeError=true`

#### Scenario: Bypass flag unset
- **WHEN** a shipment is created without the flag
- **THEN** the request URL contains no `bypassLabelfreeError` parameter

### Requirement: Labelfree and PLT bypass flags are mutually exclusive
Creating a shipment with both `bypassLabelfreeError` and `bypassPLTError` enabled SHALL be rejected before any request is sent. DHL rejects this combination with error 7183.

#### Scenario: Both bypass flags
- **WHEN** a shipment is created with bypass-labelfree and bypass-PLT both set to true
- **THEN** an invalid-argument error is raised and no HTTP request is made

#### Scenario: One flag false
- **WHEN** a shipment is created with bypass-labelfree true and bypass-PLT false
- **THEN** the request is sent with both parameters as given

### Requirement: Invoice numbers are unique across export declarations
The builder SHALL reject a shipment whose export declarations share an invoice number. DHL rejects such a shipment with error 7188.

#### Scenario: Duplicate invoice numbers
- **WHEN** two export declarations both use invoice number `INV-1`
- **THEN** `build()` fails with an invalid-request error naming the second declaration's `invoice.number`

### Requirement: Content description is meaningful
The builder SHALL reject a content description that is empty or consists only of whitespace and pipe (`|`) characters. DHL rejects such a description with error 7191.

#### Scenario: Pipes only
- **WHEN** the content description is `" || "`
- **THEN** `build()` fails with an invalid-request error naming `content.description`

#### Scenario: Normal description
- **WHEN** the content description is `"Cotton T-shirts | 3 pcs"`
- **THEN** it is accepted unchanged

### Requirement: Created shipment documents expose their invoice number
Each document in a Create Shipment response SHALL expose the optional `invoiceNumber` DHL returns, linking a generated invoice to its export declaration.

#### Scenario: Invoice document
- **WHEN** the response contains a document with `typeCode: "invoice"` and `invoiceNumber: "INV-1"`
- **THEN** the corresponding result document reports invoice number `INV-1`

#### Scenario: Label document
- **WHEN** a response document has no `invoiceNumber`
- **THEN** the result document reports no invoice number

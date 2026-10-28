# Spec Delta

## Purpose

Defines which DHL code values the library accepts for typed fields and which DHL source each value list is taken from, so enums stay aligned with what the live API accepts.

## ADDED Requirements

### Requirement: Code lists follow the current reference data workbook
Each typed code field backed by a reference-data sheet SHALL accept exactly the codes in that sheet of `ExpressReferenceData_2026-10-25.xlsx`. The one exception is codes kept under the two-of-three-sources rule. Codes the workbook no longer lists SHALL NOT be accepted.

#### Scenario: Retired shipment reference type
- **WHEN** a caller tries to use shipment reference type `AAO`, which the 2026-10-25 workbook no longer lists
- **THEN** no typed value exists for it, and constructing it from the string fails

#### Scenario: Retired package type
- **WHEN** a caller tries to use package type `WB1`
- **THEN** no typed value exists for it

#### Scenario: Current code
- **WHEN** a caller uses shipment reference type `AFM`, which the workbook lists
- **THEN** the value is accepted and serialized unchanged

### Requirement: Disagreements between DHL sources are settled by majority
When the workbook, the current OpenAPI spec and the current reference data guide disagree on a code, the library SHALL accept the code if at least two of the three list it. Each such code SHALL carry a source note in its docblock.

#### Scenario: CE1 package type
- **WHEN** the OpenAPI spec and the reference guide list package type `CE1` but the workbook does not
- **THEN** package type `CE1` is accepted

#### Scenario: AAJ package reference
- **WHEN** the workbook and the reference guide list package reference type `AAJ` but the OpenAPI spec does not
- **THEN** package reference type `AAJ` is accepted

### Requirement: Registration number type accepts FSR
Registration numbers SHALL accept type code `FSR` (Food Safety Registration) for every customer role that carries registration numbers.

#### Scenario: Food safety registration
- **WHEN** an importer is created with a registration number of type `FSR`
- **THEN** the payload contains `registrationNumbers[].typeCode: "FSR"`

### Requirement: Invoice and line item references accept the new codes
Invoice customer references SHALL accept type code `AKO`. Line item customer references SHALL accept type codes `AKO`, `MF` and `SRV`.

#### Scenario: Line item service reference
- **WHEN** a line item is created with a customer reference of type `SRV`
- **THEN** the serialized line item contains `customerReferences[].typeCode: "SRV"`

#### Scenario: Invoice AKO reference
- **WHEN** an invoice is created with a customer reference of type `AKO`
- **THEN** the serialized invoice contains `customerReferences[].typeCode: "AKO"`

### Requirement: Service and dangerous goods codes include the 2026-10-25 additions
Value-added service codes SHALL include every code in the workbook's `serviceCode` sheet, for example `5A`, `F1`, `YA` and `YL`. Dangerous goods SHALL accept content ID `A08` with service code `H8`. The retired service codes `U7`, `U8` and `U9` SHALL NOT be accepted.

#### Scenario: GoGreen Plus service
- **WHEN** a shipment is built with value-added service `F1`
- **THEN** the payload contains `valueAddedServices[].serviceCode: "F1"`

#### Scenario: ADR special provision
- **WHEN** a dangerous goods entry uses content ID `A08`
- **THEN** it is accepted, and `H8` is recognised as a dangerous goods service code

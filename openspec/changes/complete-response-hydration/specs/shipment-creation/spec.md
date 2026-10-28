# Spec Delta

## Purpose

Covers how callers build and submit DHL Create Shipment requests, the validation applied before sending, and the results returned from a created shipment.

## ADDED Requirements

### Requirement: Created shipment exposes warnings
The Create Shipment result SHALL expose the list of warnings DHL returns with a successful response. When there are none, the list is empty.

#### Scenario: Soft validation warning
- **WHEN** DHL answers 201 with `warnings: ["some values provided may be invalid"]`
- **THEN** the result exposes exactly that warning

### Requirement: Created shipment exposes charges
The Create Shipment result SHALL expose `shipmentCharges`: for each currency type, the currency, total price and a service breakdown (name, price, type code).

#### Scenario: Rate estimates requested
- **WHEN** the shipment is created with rate estimates requested and DHL returns charges in `BILLC` with two breakdown lines
- **THEN** the result exposes one charge in currency type `BILLC` with two breakdown items

### Requirement: Created shipment exposes delivery estimate and URLs
The Create Shipment result SHALL expose the estimated delivery date with its type, the on-demand delivery URL, and the shipment `url` when DHL returns them.

#### Scenario: Estimated delivery requested
- **WHEN** DHL returns `estimatedDeliveryDate: {estimatedDeliveryDate: "2026-11-03", estimatedDeliveryType: "QDDC"}`
- **THEN** the result exposes that date and type

#### Scenario: On-demand delivery URL
- **WHEN** DHL returns `onDemandDeliveryURL`
- **THEN** the result exposes it

### Requirement: Created shipment exposes barcode information
The Create Shipment result SHALL expose `barcodeInfo`: shipment identification number, origin/destination/service-type and routing barcode contents, and per-piece tracking number barcodes with their reference numbers.

#### Scenario: Custom label rendering
- **WHEN** DHL returns barcode info with two tracking number barcodes
- **THEN** the result exposes the three barcode contents and two tracking number barcodes in order

### Requirement: Created shipment exposes shipment details
The Create Shipment result SHALL expose each `shipmentDetails` entry: service handling feature codes, volumetric weight, billing and service content codes, origin and destination service areas, DHL routing code and data ID, delivery date and time codes, product short name, value-added services, and pickup details (cut-off, earliest and latest pickup, transit days, pickup and delivery days of week).

#### Scenario: Routing data
- **WHEN** DHL returns a shipment detail with `dhlRoutingCode` and an origin service area `{serviceAreaCode: "PRG", facilityCode: "PRG"}`
- **THEN** the result exposes the routing code and the origin service area

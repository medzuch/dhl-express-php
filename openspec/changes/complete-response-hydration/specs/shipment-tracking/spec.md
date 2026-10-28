# Spec Delta

## Purpose

Covers retrieving tracking information for single and multiple DHL Express shipments and pieces, and the tracking data returned to callers.

## ADDED Requirements

### Requirement: Tracking exposes shipment-level details
Each tracked shipment SHALL expose, when DHL returns them, its shipment timestamp, product code, total weight with unit, number of pieces, estimated delivery date, shipper references, child shipment identification numbers and controlled-access data codes, in addition to tracking number, status, description and events.

#### Scenario: Full shipment response
- **WHEN** DHL returns a shipment with `productCode: "P"`, `totalWeight: 2.5`, `numberOfPieces: 2` and `estimatedDeliveryDate`
- **THEN** the tracking result exposes product code `P`, total weight 2.5 with its unit, 2 pieces, and the estimated delivery date

#### Scenario: Minimal shipment response
- **WHEN** DHL returns only tracking number, status, description and events
- **THEN** hydration succeeds and the optional details are reported as absent

### Requirement: Tracking exposes shipper and receiver details
Each tracked shipment SHALL expose shipper and receiver details: name, postal address (city, county, postal code, province code, country code) and service areas (code, description, and the facility, inbound or outbound sort codes DHL provides).

#### Scenario: Receiver service area
- **WHEN** DHL returns receiver details with a service area that has `facilityCode` and `inboundSortCode`
- **THEN** the receiver's service area exposes both values

### Requirement: Tracking exposes pieces
When DHL returns `pieces`, each piece SHALL expose its number, type code, piece tracking number, description, declared, dimensional and actual weight, declared and actual dimensions, unit, shipper references, and its own events.

#### Scenario: Piece tracking view
- **WHEN** a caller tracks with a piece-level tracking view and DHL returns two pieces, each with events
- **THEN** the result exposes two pieces, each with its tracking number and its events

### Requirement: Tracking events expose offset, location and signatory
Each tracking event (shipment-level and piece-level) SHALL expose, when present, its GMT offset, service areas (code and description) and `signedBy`.

#### Scenario: GMT offset requested
- **WHEN** a caller requests GMT offset per event and DHL returns `GMTOffset: "+01:00"` on an event
- **THEN** that event exposes GMT offset `+01:00`

#### Scenario: Delivered event
- **WHEN** an event has `signedBy: "J. DOE"`
- **THEN** that event exposes signatory `J. DOE`

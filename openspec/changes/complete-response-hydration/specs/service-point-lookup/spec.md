# Spec Delta

## Purpose

Covers searching for DHL Express service points and the operational details returned for each service point.

## ADDED Requirements

### Requirement: Service points expose capabilities
Each service point SHALL expose its capabilities (drop-off, collection, international/domestic shipping, label printing, receiver-paid, payment methods, parking and disabled access, piece weight and dimension limits with units, and capability codes) when DHL returns them.

#### Scenario: Drop-off capable point
- **WHEN** DHL returns a service point with `shipmentDropOff: true` and `pieceWeightLimit: 30` in `KG`
- **THEN** the service point exposes drop-off support and a 30 kg piece weight limit

### Requirement: Service points expose contact details and limitations
Each service point SHALL expose contact details (phone number, link, contact type), shipment limitations (max pieces, max shipment weight, with units), per-piece limitations (size limits, max piece weight), and charge code.

#### Scenario: Shipment limits
- **WHEN** DHL returns `shipmentLimitations.maxShipmentWeight` of 70 in `KG`
- **THEN** the service point exposes a 70 kg max shipment weight

### Requirement: Service points expose schedule and status
Each service point SHALL expose holiday opening and closing periods, time zone, work week start, located-at text, and capacity status (severity and description).

#### Scenario: Holiday closure
- **WHEN** DHL returns a holiday closed period from `2026-12-24` to `2026-12-26`
- **THEN** the service point exposes that closed period

#### Scenario: Marketing fields ignored
- **WHEN** DHL returns `promotion` and `translations` data
- **THEN** hydration succeeds and those fields are not exposed

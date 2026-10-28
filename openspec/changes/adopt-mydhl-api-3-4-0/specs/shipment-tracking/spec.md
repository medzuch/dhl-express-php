# Spec Delta

## Purpose

Covers retrieving tracking information for single and multiple DHL Express shipments and pieces, and the tracking data returned to callers.

## ADDED Requirements

### Requirement: Multi-shipment tracking can request per-event GMT offsets
All multi-shipment tracking operations (by tracking numbers, by piece IDs, by reference) SHALL accept an optional flag that sends `requestGMTOffsetPerEvent`, matching single-shipment tracking.

#### Scenario: Flag enabled
- **WHEN** a caller tracks several shipments with GMT offset per event enabled
- **THEN** the `/tracking` request URL contains `requestGMTOffsetPerEvent=true`

#### Scenario: Flag omitted
- **WHEN** a caller tracks several shipments without the flag
- **THEN** the request URL contains no `requestGMTOffsetPerEvent` parameter

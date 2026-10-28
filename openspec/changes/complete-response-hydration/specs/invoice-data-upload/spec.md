# Spec Delta

## Purpose

Covers uploading commercial invoice data to DHL, either for an existing shipment or as a standalone invoice, and the result returned to callers.

## ADDED Requirements

### Requirement: Standalone invoice upload returns its result
Uploading standalone invoice data SHALL return a result exposing the `status` and `warnings` from DHL's 200 response, instead of returning nothing.

#### Scenario: Upload with warnings
- **WHEN** DHL answers 200 with `{"status": "OK", "warnings": ["some values provided may be invalid"]}`
- **THEN** the result exposes status `OK` and that one warning

#### Scenario: Upload without warnings
- **WHEN** DHL answers 200 with `{"status": "OK"}`
- **THEN** the result exposes status `OK` and an empty warnings list

### Requirement: Per-shipment invoice upload returns nothing
Uploading invoice data for an existing shipment SHALL keep returning no value, because DHL's 200 response has no body.

#### Scenario: Per-shipment upload
- **WHEN** DHL answers 200 with an empty body to a per-shipment upload
- **THEN** the call completes without returning a value or raising an exception

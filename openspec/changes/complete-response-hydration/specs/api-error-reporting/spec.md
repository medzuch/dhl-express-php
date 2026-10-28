# Spec Delta

## Purpose

Defines the diagnostic information the library attaches to DHL API errors so callers can handle them and escalate them to DHL support.

## ADDED Requirements

### Requirement: API exceptions carry the DHL invocation ID
Every exception raised for a DHL error response SHALL expose the value of the `Invocation-Id` response header, or no value when the header is absent.

#### Scenario: Header present
- **WHEN** DHL answers 422 with header `Invocation-Id: 3f2c...`
- **THEN** the raised validation exception exposes invocation ID `3f2c...`

#### Scenario: Header absent
- **WHEN** DHL answers 500 without an `Invocation-Id` header
- **THEN** the raised server exception exposes no invocation ID

#### Scenario: Header case
- **WHEN** DHL sends the header as `invocation-id`
- **THEN** the exception still exposes its value

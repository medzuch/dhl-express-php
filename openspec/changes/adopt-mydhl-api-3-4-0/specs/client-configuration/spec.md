# Spec Delta

## Purpose

Defines the client-wide settings the library applies to every DHL request, starting with the MyDHL API interface version it speaks.

## ADDED Requirements

### Requirement: Default MyDHL API version is 3.4.0
The client SHALL send the `x-version` header with value `3.4.0` on every request unless the caller configures a different value. All request payloads the library produces SHALL conform to the MyDHL API 3.4.0 schema.

#### Scenario: Default configuration
- **WHEN** a client is created without an explicit API version and any API call is made
- **THEN** the outgoing request carries the header `x-version: 3.4.0`

#### Scenario: Explicit override
- **WHEN** a client is created with an explicit API version `3.4.1`
- **THEN** the outgoing request carries the header `x-version: 3.4.1`

#### Scenario: Empty version rejected
- **WHEN** a client is created with an empty API version
- **THEN** configuration fails with an invalid-argument error before any request is sent

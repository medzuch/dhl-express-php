# Spec Delta

## Purpose

Covers booking, updating and cancelling DHL Express courier pickups and the results the library returns for each operation.

## ADDED Requirements

### Requirement: Pickup cancellation returns its outcome
Cancelling a pickup SHALL return a result exposing the `status`, `title`, `detail`, `instance` and `message` DHL returns on success. DHL uses this to signal that the pickup was cancelled, was already cancelled, or had already been completed. Error responses SHALL continue to raise exceptions.

#### Scenario: Pickup cancelled
- **WHEN** DHL answers a cancellation with HTTP 200 and a body describing the cancellation
- **THEN** the caller receives a result whose message and instance (and any status, title and detail) match the body

#### Scenario: Empty success body
- **WHEN** DHL answers a cancellation with HTTP 200 and an empty body
- **THEN** the caller receives a result with every field empty, and no exception is raised

#### Scenario: Unknown pickup
- **WHEN** DHL answers a cancellation with HTTP 404
- **THEN** a not-found exception is raised as before

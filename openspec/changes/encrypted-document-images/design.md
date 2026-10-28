# Design

## Context

See proposal.md (Why). Relevant current state:
- `DocumentImage` (plain PLT images) is a DTO with `content`, a `DocumentImageTypeCode` and a `DocumentImageFormat`.
- `CreateShipmentRequest::$documentImages` is a list. `CreateShipmentBuilder::withDocumentImage()` appends to it.
- `UploadImageRequest` requires `documentImages`.
- The 3.4.0 spec defines encryption metadata (`algorithm: aes-256-cbc`, `symmetricKey`, `keyId` = "Key Pair ID for the public key used") but does not document how the symmetric key is protected, or how keys are exchanged. That is handled during DHL's PII onboarding.

## Goals / Non-Goals

**Goals:**
- Typed, validated DTOs for encrypted images and encryption parameters on both endpoints.
- Request-level consistency checks (images ⇔ encryption) before anything is sent.

**Non-Goals:**
- Performing encryption or key wrapping, or managing DHL public keys.
- Detecting whether the account is PII-onboarded; DHL's error response covers that.

## Decisions

### D1. Carry ciphertext only; no crypto helper
The DTOs accept already-encrypted base64 strings. A built-in `ext-openssl` helper was rejected because the spec doesn't define the key-wrapping scheme for `symmetricKey`, so any helper would encode guesses about a security-critical protocol. Holding keys also widens the library's security surface. If DHL publishes the scheme, a helper can be added later as a separate class, without changing these DTOs.

### D2. New enums rather than reusing existing ones
- `EncryptedDocumentImageTypeCode` (`DLI`, `NID`, `ORD`, `PAS`): these codes are disjoint from `DocumentImageTypeCode` (`INV`, `PNV`, …), and merging them would let callers send invalid combinations.
- `CustomerRoleType` (`shipper`, `receiver`, `buyer`, `importer`, `exporter`, `seller`, `payer`, `manufacturer`, `ultimateConsignee`, `broker`).
- `EncryptionAlgorithm` (`aes-256-cbc`).
- `DocumentImageFormat` is reused, because the values are identical.

All values come from the OpenAPI 3.4.0 spec. Neither `ExpressReferenceData_2026-10-25.xlsx` nor the 3.4.0 reference data guide has a sheet for them, so each enum's docblock cites the OpenAPI schema it mirrors.

### D3. `DocumentEncryption` DTO name
The wire key is `encryption`. The DTO is named `DocumentEncryption` so it isn't mistaken for a transport-level concept, and its docblock links it to `encryptedDocumentImages`. `algorithm` defaults to `EncryptionAlgorithm::Aes256Cbc`, matching the spec default.

### D4. Where the consistency checks live
- `UploadImageRequest` is built directly by callers, so its constructor enforces: at least one image of either kind, encrypted ≤ 10, encrypted images ⇔ encryption.
- For Create Shipment, `CreateShipmentBuilder` gets `withEncryptedDocumentImage()` (append) and `withDocumentEncryption()`. The same checks run in `build()` and are reported together with the other builder errors.
- `CreateShipmentRequest` also checks images ⇔ encryption in its constructor, so callers who bypass the builder are still covered.

### D5. No client-side size limit; PII errors via the existing mapping
The 3.4.0 reference data guide makes the encrypted-content size limit account-configurable ("cannot exceed '<configure>MB'", 7180), so any hard-coded limit would be wrong for some accounts.

PII failures (7171–7181) arrive as 4xx responses. The existing `DhlErrorMapper` already turns them into `DhlValidationException` / `DhlAuthorizationException` with `$dhlErrorCode` set. No new exception classes are added; the README gets a troubleshooting table mapping each code to its cause and remedy.

Adding `DhlErrorCode` enum cases for these codes is deferred until callers ask to branch on them, per the CLAUDE.md "curate on demand" rule.

### D6. Secret hygiene
Validation messages reference field names and lengths only. The new DTOs don't implement `__toString` or `JsonSerializable` beyond `toArray()`. A unit test asserts that an exception message contains no sample key or content string.

## Risks / Trade-offs

- [Callers can't produce valid ciphertext without DHL's onboarding documentation] → The README section points to onboarding through a DHL representative and states that the library only transports the payload.
- [The sandbox may reject PII uploads for non-onboarded accounts] → Integration tests for this feature are opt-in via a separate `DHL_PII_ENABLED` env flag on top of `DHL_API_KEY`.
- [The spec's `minProperties: 1` and `anyOf` on upload-image are loosely specified] → We enforce the stricter, documented intent ("either documentImages, encryptedDocumentImages, or both").

## Migration Plan

This is additive apart from `UploadImageRequest`, whose `documentImages` gets an `[]` default, so existing positional calls with plain images keep working. It ships as part of 0.4.x after `adopt-mydhl-api-3-4-0`.

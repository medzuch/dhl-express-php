# Tasks

Prerequisite: `adopt-mydhl-api-3-4-0` is merged. TDD per CLAUDE.md: test first, then implementation.

## 1. Enums and DTOs

- [ ] 1.1 Add enums `EncryptedDocumentImageTypeCode`, `CustomerRoleType` and `EncryptionAlgorithm` with spec-sourced docblocks; verify with `tryFrom` tests covering every value
- [ ] 1.2 Add the `DocumentEncryption` DTO (algorithm defaulting to aes-256-cbc, non-empty symmetricKey and keyId); verify with `DocumentEncryptionTest` (serialization, empty-field rejection, no secret in exception message)
- [ ] 1.3 Add the `EncryptedDocumentImage` DTO (required fields, 2-letter country code via the existing `CountryCode` VO, division code 2–35); verify with `EncryptedDocumentImageTest` (serialization, each invalid field, no content/IV in exception message)

## 2. Create Shipment

- [ ] 2.1 `CreateShipmentRequest`: add `encryptedDocumentImages` and `?DocumentEncryption $encryption`, serialized only when present, with the constructor images ⇔ encryption check; verify with `CreateShipmentRequestTest` cases for none, both, and each mismatch
- [ ] 2.2 `CreateShipmentBuilder`: add `withEncryptedDocumentImage()` and `withDocumentEncryption()`, with `build()` checks for the ≤ 10 limit and images ⇔ encryption; verify with builder tests for 1, 10, 11 images and each mismatch, reported alongside other errors

## 3. Upload Image

- [ ] 3.1 `UploadImageRequest`: make `documentImages` default to `[]`, add `encryptedDocumentImages` and `?DocumentEncryption`, and enforce at least one image, ≤ 10 encrypted, and images ⇔ encryption; verify with `UploadImageRequestTest` for plain-only, encrypted-only, both, none, and mismatch
- [ ] 3.2 Assert the `ShipmentApi::uploadImage()` payload for the encrypted-only and mixed cases; verify with `ShipmentApiUploadGetTest` JSON body assertions

## 4. Documentation

- [ ] 4.1 Add a README section "Encrypted (PII) document images" with a builder example, a note that DHL onboarding is required, and a note that the library does not encrypt; verify the snippet compiles against the API (php -l on extracted snippet)
- [ ] 4.2 Add a README "PII upload troubleshooting" table for errors 7171–7181 (cause and remedy per code, e.g. 7172 → contact DHL for onboarding; 7174 → key expired, request a new key pair); verify each code from the 3.4.0 reference data guide appears exactly once
- [ ] 4.3 Add `ShipmentApiUploadGetTest` cases where a mocked 422 carrying code 7172 or 7180 raises an exception exposing that `dhlErrorCode`, and a 12 MB encrypted image is accepted client-side; verify both tests pass
- [ ] 4.4 Add a CHANGELOG entry under the next 0.4.x version listing the new DTOs/enums and the `UploadImageRequest` default change; verify the entry exists

## 5. Integration

- [ ] 5.1 Add an opt-in integration test (skipped unless `DHL_API_KEY` and `DHL_PII_ENABLED` are set) uploading one encrypted image to a sandbox shipment; verify it is skipped by default and `make check` is green with ≥ 90% coverage on `src/`

## Workflow follow-up

- Archive after release.

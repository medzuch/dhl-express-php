# Proposal

## Why

MyDHL API 3.4.0 lets onboarded customers send identity documents (passport, national ID, driver licence, organisation registration) as encrypted PII document images. They can be sent with Create Shipment or uploaded afterwards. Customs brokers increasingly ask for these documents, and today callers of this library have no typed way to send them.

## What Changes

- Create Shipment accepts up to 10 encrypted document images plus the `encryption` parameters (algorithm, base64 symmetric key, key ID) needed to decrypt them.
- Upload Image accepts encrypted document images and encryption parameters. It now accepts plain document images, encrypted ones, or both, instead of requiring plain images.
- The library carries ciphertext the caller has already produced. It does not encrypt, hold keys, or add a crypto dependency.
- **BREAKING** (minor): `UploadImageRequest` no longer requires a non-empty `documentImages` list. Callers passing only plain images are unaffected; the constructor shape changes because the new arguments are optional named arguments.

## Capabilities

### New Capabilities

- `shipment-document-images`: Sending plain and encrypted (PII) document images with a new shipment or to an existing one, including the encryption metadata DHL needs.

### Modified Capabilities

_None. The baseline specs come from `adopt-mydhl-api-3-4-0`, and this change touches none of its requirements._

## Impact

- **Code:** new DTOs `EncryptedDocumentImage` and `DocumentEncryption`; new enums for the PII document type (`DLI`, `NID`, `ORD`, `PAS`), the customer role type, and the encryption algorithm (`aes-256-cbc`). Updated: `CreateShipmentRequest`, `CreateShipmentBuilder`, `UploadImageRequest`.
- **Tests:** DTO, builder and `ShipmentApi` upload unit tests, plus fixtures. Integration tests are skipped unless the sandbox account is PII-onboarded.
- **Docs:** README section on encrypted document images, CHANGELOG.
- **Dependencies:** none. **Ordering:** requires `adopt-mydhl-api-3-4-0` (x-version 3.4.0).

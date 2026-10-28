# Spec Delta

## Purpose

Covers sending document images to DHL, plain paperless-trade images and encrypted PII identity documents, either with a new shipment or uploaded to an existing one.

## ADDED Requirements

### Requirement: Encrypted document images carry pre-encrypted content
An encrypted document image SHALL require a document type (`DLI`, `NID`, `ORD` or `PAS`), an image format (`PDF`, `PNG`, `GIF`, `TIFF`, `JPEG`), a base64 initialization vector, base64 encrypted content, a customer role type and a two-letter source country code. It SHALL accept an optional division code of 2–35 characters. The library SHALL NOT encrypt, decrypt or inspect the content.

#### Scenario: Valid encrypted image
- **WHEN** a caller creates an encrypted passport image for role `receiver` from country `DE`
- **THEN** it serializes with `typeCode: "PAS"`, `customerRoleType: "receiver"`, `countryCode: "DE"`, and the given `initializationVector`, `content` and `imageFormat`

#### Scenario: Invalid country code
- **WHEN** a caller creates an encrypted image with country code `DEU`
- **THEN** construction fails with an invalid-argument error

#### Scenario: Division code length
- **WHEN** a caller sets a 1-character division code
- **THEN** construction fails with an invalid-argument error

### Requirement: Encryption parameters accompany encrypted images
Encryption parameters SHALL require an algorithm (only `aes-256-cbc` is defined), a base64 symmetric key and a key ID. A request that contains encrypted document images SHALL also contain encryption parameters, and a request with encryption parameters SHALL contain at least one encrypted image.

#### Scenario: Images without encryption parameters
- **WHEN** a Create Shipment or Upload Image request has encrypted images but no encryption parameters
- **THEN** the request is rejected before sending with an error naming `encryption`

#### Scenario: Encryption parameters without images
- **WHEN** a request has encryption parameters but no encrypted images
- **THEN** the request is rejected before sending with an error naming `encryptedDocumentImages`

### Requirement: Create Shipment accepts encrypted document images
Create Shipment SHALL accept up to 10 encrypted document images alongside any plain document images. When present, it SHALL serialize them as `encryptedDocumentImages` with a top-level `encryption` object.

#### Scenario: Shipment with encrypted ID
- **WHEN** a shipment is built with one encrypted image and encryption parameters
- **THEN** the payload contains `encryptedDocumentImages` with one entry and `encryption` with `algorithm`, `symmetricKey` and `keyId`

#### Scenario: Too many encrypted images
- **WHEN** a shipment is built with 11 encrypted images
- **THEN** `build()` fails with an invalid-request error naming `encryptedDocumentImages`

#### Scenario: No encrypted images
- **WHEN** a shipment is built without encrypted images
- **THEN** the payload contains neither `encryptedDocumentImages` nor `encryption`

### Requirement: Upload Image accepts plain, encrypted, or both
An Upload Image request SHALL contain at least one plain or encrypted document image. It SHALL serialize only the image lists that are non-empty, and SHALL include `encryption` exactly when encrypted images are present. Encrypted images SHALL be limited to 10.

#### Scenario: Encrypted only
- **WHEN** an upload request has two encrypted images, encryption parameters and no plain images
- **THEN** the payload contains `encryptedDocumentImages` and `encryption` and has no `documentImages` key

#### Scenario: Both kinds
- **WHEN** an upload request has one plain and one encrypted image
- **THEN** the payload contains both `documentImages` and `encryptedDocumentImages`

#### Scenario: No images at all
- **WHEN** an upload request has neither plain nor encrypted images
- **THEN** construction fails with an invalid-argument error

### Requirement: Encrypted image size is left to DHL
The library SHALL NOT enforce a maximum size on encrypted image content, because DHL configures the limit per account (error 7180). It SHALL enforce only the presence and format constraints defined by the API schema.

#### Scenario: Large encrypted image
- **WHEN** a caller creates an encrypted image whose content is 12 MB of base64
- **THEN** construction succeeds, and any size rejection comes from DHL as an API exception carrying error code 7180

### Requirement: PII error codes reach the caller
When DHL rejects a PII upload, the exception SHALL carry DHL's error code (7171–7181) so callers can tell these cases apart:
- the account is not onboarded (7172)
- the key ID is invalid or expired (7173, 7174)
- the algorithm does not match the key (7175)
- the symmetric key or IV is invalid (7176, 7177)
- the content is wrongly encrypted or invalid (7178, 7179)
- the content is too large (7180)
- the content does not match the image format (7181)

#### Scenario: Account not onboarded
- **WHEN** DHL answers an encrypted upload with an error carrying code 7172
- **THEN** the raised exception exposes DHL error code `7172`

### Requirement: Encrypted content is never logged or echoed
The library SHALL NOT include encrypted image content, initialization vectors or symmetric keys in exception messages or debug output it produces.

#### Scenario: Validation error message
- **WHEN** construction of an encrypted image fails validation
- **THEN** the exception message names the invalid field but contains no content, IV or key value

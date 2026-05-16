# DHL Express PHP Library — Claude Instructions

A standalone, framework-agnostic PHP 8.3 library wrapping the DHL Express MyDHL API 3.2.2.

- **Package:** `medzuch/dhl-express-php`
- **Namespace:** `Medzuch\DhlExpress`
- **License:** MIT
- **Repo:** github.com/medzuch/dhl-express-php

## Read first

DHL official documentation (precedence order — primary first):
1. **Reference data workbook:** `specs/dhl/dhl_reference_data.xlsx` — machine-readable dump from DHL's Reference Data API. **Authoritative source for value lists** (enums, codes, error messages).
2. OpenAPI spec: `specs/dhl/dhl_openapi.yaml` — authoritative for request/response shapes and field constraints.
3. Reference data PDF: `specs/dhl/dhl_reference.pdf` — narrative reference; useful for context, but treated as secondary because it contains legacy/deprecated codes the live API no longer accepts.

ALWAYS cross-reference these files when generating enums, DTOs, or error mappings. When the xlsx and the PDF disagree, the xlsx wins.

## Hard rules — apply to every change

### Code style
- PHP 8.3 with `declare(strict_types=1);` at the top of every file
- PSR-12 enforced via `php-cs-fixer` (config: `.php-cs-fixer.php`)
- PHPStan **level max** clean — no `mixed` unless truly unavoidable
- All public methods have full type declarations and docblocks
- `final` classes by default; only non-final when designed for extension

### Type design
- DTOs: `final readonly class` with constructor property promotion + named arguments
- Value Objects: self-validating, immutable, throw on invalid input
- Enums: PHP 8.1+ backed string enums (string-backed unless DHL uses ints)
- Never use raw arrays for API request/response data — always typed DTOs
- Never use static methods (untestable, can't mock)

### Architecture
- **No framework dependencies** — pure library, usable from Symfony/Laravel/standalone
- `DhlClient` is a facade that delegates — never implements business logic
- One `*Api` class per DHL domain (TrackingApi, ShipmentApi, PickupApi, etc.)
- Code against `Psr\Http\Client\ClientInterface` (PSR-18) and `Psr\Http\Message\RequestFactoryInterface` (PSR-17) — never Guzzle types directly; Guzzle is the default implementation only
- Builders for deeply-nested requests (CreateShipmentBuilder, RateRequestBuilder)

### Error handling
- **Never leak Guzzle exceptions** — always wrap in `DhlException` hierarchy
- Map both HTTP status codes AND DHL error codes to specific exceptions
- The raw DHL error code is always carried on `DhlApiException::$dhlErrorCode`. The full canonical list (~780 entries) lives in `dhl_reference_data.xlsx#returnStatusMessage`; we curate a `DhlErrorCode` enum on demand for codes callers want to branch on (e.g. `9001`, `7012`, `422`)

### Naming
- Mirror DHL's own terminology exactly — `Shipment`, `Piece`, `Waybill`, `ServicePoint`, `Pickup`, `Incoterm`, `EPOD`
- No abbreviations in public API — `getTrackingStatus()` not `getTrkStatus()`
- Use ubiquitous language from `specs/dhl/` reference materials

### Testing (TDD)
- **Write the test first**, then implementation
- Unit tests use mocked HTTP via `php-http/mock-client`
- Integration tests hit real sandbox (`https://express.api.dhl.com/mydhlapi/test`)
- Integration tests skipped unless `DHL_API_KEY` env var is set
- Target: ≥90% line coverage on `src/`
- JSON fixtures in `tests/Fixtures/` per domain

## Workflow

### Make targets
```
make up             # start containers
make down           # stop containers
make build          # rebuild image
make install        # composer install
make test               # phpunit unit suite only (integration excluded by default)
make test-integration   # phpunit integration suite (requires DHL_API_KEY)
make analyse            # phpstan analyse src/ (level max)
make analyse-tests  # phpstan analyse tests/ (level 6)
make analyse-all    # phpstan analyse src/ + tests/
make cs-fix         # fix code style via php-cs-fixer
make cs-check       # check style without modifying (dry-run)
make check          # test + analyse-all + cs-check
make shell          # enter container shell
```

### Git
- **Conventional Commits** format for ALL commit messages
    - `feat:` new feature
    - `fix:` bug fix
    - `chore:` tooling/maintenance
    - `docs:` documentation
    - `test:` test additions/changes
    - `refactor:` code restructure without behavior change
- Branch off `main`, merge via PR
- LF line endings everywhere — `.gitattributes` enforces this

## Critical reminders

1. **TDD strict** — never write implementation before its test
2. **PHPStan level max** from day one, not "later"
3. **Cross-reference DHL docs** in `specs/dhl/` for any enum value, error code, or DTO field — do not invent values
4. **Conventional Commits** — every commit message
5. **No framework code** in the library — keep it pure PHP
6. **DHL terminology** — match their docs exactly, do not invent your own names

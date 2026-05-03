# DHL Express PHP Library — Claude Instructions

A standalone, framework-agnostic PHP 8.3 library wrapping the DHL Express MyDHL API 3.2.2.

- **Package:** `medzuch/dhl-express-php`
- **Namespace:** `Medzuch\DhlExpress`
- **License:** MIT
- **Repo:** github.com/medzuch/dhl-express-php

## Read first

Full architecture, folder structure, and implementation roadmap:
@PROJECT_PLAN.md

DHL official documentation:
- OpenAPI spec: `docs/dhl/dhl_openapi.yaml`
- Reference data PDF: `docs/dhl/dhl_reference.pdf`

ALWAYS cross-reference these two files when generating enums, DTOs, or error mappings.

## Hard rules — apply to every change

### Code style
- PHP 8.3 with `declare(strict_types=1);` at the top of every file
- PSR-12 enforced via `php-cs-fixer` (config: `.php-cs-fixer.php`)
- PHPStan **level 8** clean — no `mixed` unless truly unavoidable
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
- All 200+ error codes from the reference PDF go in `DhlErrorCode` enum

### Naming
- Mirror DHL's own terminology exactly — `Shipment`, `Piece`, `Waybill`, `ServicePoint`, `Pickup`, `Incoterm`, `EPOD`
- No abbreviations in public API — `getTrackingStatus()` not `getTrkStatus()`
- Use ubiquitous language from `docs/dhl/` reference materials

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
make test           # phpunit
make analyse        # phpstan analyse src/ (level 8)
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
2. **PHPStan level 8** from day one, not "later"
3. **Cross-reference DHL docs** in `docs/dhl/` for any enum value, error code, or DTO field — do not invent values
4. **Conventional Commits** — every commit message
5. **No framework code** in the library — keep it pure PHP
6. **DHL terminology** — match their docs exactly, do not invent your own names

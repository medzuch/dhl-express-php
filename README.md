# medzuch/dhl-express-php

A clean, framework-agnostic PHP 8.3 library for the DHL Express API.

## Requirements

- PHP 8.3+
- Composer
- Docker + Docker Compose (for local development)

## Installation

```bash
composer require medzuch/dhl-express-php
```

## Development Setup

Clone the repository:

```bash
git clone git@github.com:medzuch/dhl-express-php.git
cd dhl-express-php
```

Start the Docker environment:

```bash
make build
make install
```

## Available Make Commands

| Command | Description |
|---|---|
| `make build` | Build and start Docker containers |
| `make up` | Start containers |
| `make down` | Stop containers |
| `make install` | Run composer install |
| `make test` | Run PHPUnit test suite |
| `make analyse` | Run PHPStan static analysis |
| `make check` | Run tests and analysis together |
| `make shell` | Open shell inside container |

## Environment

Copy `.env.example` to `.env` and fill in your DHL API credentials:

```bash
cp .env.example .env
```

```ini
DHL_API_KEY=your_api_key_here
DHL_API_SECRET=your_api_secret_here
DHL_ACCOUNT_NUMBER=your_account_number
DHL_BASE_URL=https://express.api.dhl.com/mydhlapi
```

## License

MIT
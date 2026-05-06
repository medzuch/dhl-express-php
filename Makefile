.PHONY: help setup install verify test test-integration analyse analyse-tests analyse-all check cs-fix cs-check shell down up build

.DEFAULT_GOAL := help

help:
	@echo ""
	@echo "Available commands:"
	@echo "  make setup          One-time bootstrap: create .env, build image, install deps"
	@echo "  make build          Build and start Docker containers"
	@echo "  make up             Start containers"
	@echo "  make down           Stop containers"
	@echo "  make install        Run composer install"
	@echo "  make verify         Check PHP version"
	@echo "  make test           Run PHPUnit unit suite (integration excluded)"
	@echo "  make test-integration Run integration tests (requires DHL_API_KEY)"
	@echo "  make analyse        Run PHPStan on src/"
	@echo "  make analyse-tests  Run PHPStan on tests/"
	@echo "  make analyse-all    Run PHPStan on src/ and tests/"
	@echo "  make check          Run tests, full analysis, and code style check"
	@echo "  make cs-fix         Fix code style issues"
	@echo "  make cs-check       Check code style without making changes"
	@echo "  make shell          Open shell inside container"

setup: .env build install
	@echo ""
	@echo "Setup complete. Edit .env with your DHL credentials, then run: make test"

.env: .env.example
	@cp -n .env.example .env
	@echo ".env created from .env.example — fill in DHL_API_KEY / DHL_API_SECRET / DHL_ACCOUNT_NUMBER."

up:
	docker compose up -d

build:
	docker compose up -d --build

down:
	docker compose down

install:
	docker compose exec app composer install

verify:
	docker compose exec app php -v

test:
	docker compose exec app ./vendor/bin/phpunit --testsuite Unit --exclude-group integration --no-coverage

test-integration:
	docker compose exec app ./vendor/bin/phpunit --testsuite Integration --group integration --no-coverage

cs-fix:
	docker compose exec app ./vendor/bin/php-cs-fixer fix

cs-check:
	docker compose exec app ./vendor/bin/php-cs-fixer fix --dry-run --diff

analyse:
	docker compose exec app ./vendor/bin/phpstan analyse --configuration phpstan.neon --memory-limit=512M

analyse-tests:
	docker compose exec app ./vendor/bin/phpstan analyse --configuration phpstan-tests.neon --memory-limit=512M

analyse-all: analyse analyse-tests

check: test analyse-all cs-check

shell:
	docker compose exec app sh
.PHONY: help install verify test test-integration analyse analyse-tests analyse-all check cs-fix cs-check shell down up build

.DEFAULT_GOAL := help

help:
	@echo ""
	@echo "Available commands:"
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
	docker compose exec app ./vendor/bin/phpunit --testsuite Unit

test-integration:
	docker compose exec app ./vendor/bin/phpunit --testsuite Integration

cs-fix:
	docker compose exec app ./vendor/bin/php-cs-fixer fix src/ tests/

cs-check:
	docker compose exec app ./vendor/bin/php-cs-fixer fix src/ tests/ --dry-run --diff

analyse:
	docker compose exec app ./vendor/bin/phpstan analyse --configuration phpstan.neon

analyse-tests:
	docker compose exec app ./vendor/bin/phpstan analyse --configuration phpstan-tests.neon

analyse-all: analyse analyse-tests

check: test analyse-all cs-check

shell:
	docker compose exec app sh
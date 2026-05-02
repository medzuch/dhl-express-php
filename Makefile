.PHONY: install verify test analyse check shell down up build

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
	docker compose exec app ./vendor/bin/phpunit

analyse:
	docker compose exec app ./vendor/bin/phpstan analyse src/

check: test analyse

shell:
	docker compose exec app sh
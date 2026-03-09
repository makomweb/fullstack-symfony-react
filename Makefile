APP_NAME = fullstack-symfony-react
VERSION = 0.9.1

.DEFAULT_GOAL := help

.PHONY: build dev prod backend-image frontend-image grafana up down reset \
	reset-worker reset-app init composer-install create-database create-schema \
	load-fixtures init-test create-test-database create-test-schema composer shell \
	qa sa cs test backend-test frontend-test arch clear cache-clear cache-pool-clear \
	build-viz svg build-dot build-svg circle maintenance maintain show-composer-updates \
	update-composer-dependencies update-npm-dependencies coverage frontend-shell open help

## Start development environment (build images, start containers, init, open browser)
start: dev grafana up init open

## Build development Docker image
dev:
	docker build . -f ./build/php/Dockerfile --target dev -t ${APP_NAME}-dev:${VERSION}
	
## Build production images
prod: backend-image frontend-image

## Build backend image
backend-image:
	@echo "Build backend image"
	docker build . -f ./build/php/Dockerfile --target prod --no-cache -t ${APP_NAME}:${VERSION}

## Build frontend image
frontend-image:
	@echo "Build frontend image"
	docker build . -f ./build/node/Dockerfile --target prod --no-cache -t ${APP_NAME}-web:${VERSION}

grafana:
	@echo "Build grafana image"
	docker build . -f ./build/grafana/Dockerfile -t ${APP_NAME}-grafana:${VERSION}

## Start all services using docker compose
up:
	docker compose up -d --remove-orphans

## Stop all services and remove containers
down:
	docker compose down

## Reset all services
reset: reset-worker reset-app

## Reset worker
reset-worker:
	@echo "Reset worker"
	docker compose restart worker

## Reset app
reset-app:
	@echo "Reset app"
	docker compose restart app

## Initialize project (install dependencies, create database, schema, load fixtures)
init: composer-install create-database create-schema load-fixtures

## Install composer dependencies
composer-install:
	@echo "Install composer dependencies"
	docker compose exec -it app composer install

## Create database
create-database:
	@echo "Create database"
	docker compose exec -it app bin/console doctrine:database:create --if-not-exists

## Load test fixtures
load-fixtures:
	@echo "Load fixtures"
	docker compose exec -it app bin/console doctrine:fixtures:load -q

## Create database schema
create-schema:
	@echo "Create database schema"
	docker compose exec -it app bin/console doctrine:schema:update --force

## Initialize test environment
init-test: create-test-database create-test-schema

## Create test database
create-test-database:
	@echo "Create database"
	docker compose exec -it app bin/console doctrine:database:create --env=test --if-not-exists

## Create test database schema
create-test-schema:
	@echo "Create database schema"
	docker compose exec -it app bin/console doctrine:schema:update --env=test --force

## Run composer command (use cmd=<command>)
composer:
	@echo "Run composer"
	docker compose exec -it app composer $(cmd)

shell:
	docker compose exec -it app bash
	
## Run quality assurance (phpstan, php-cs-fixer)
qa:
	docker compose exec -it app composer qa
## Run static analysis
sa:
	docker compose exec -it app vendor/bin/phpstan analyse --memory-limit=1G
## Run code style fixer
cs:
	docker compose exec -it app vendor/bin/php-cs-fixer fix

## Run all tests
test: init-test backend-test

## Run backend tests
backend-test:
	docker compose exec -it app composer test

## Run frontend tests
frontend-test:
	docker compose exec -it frontend npm test

## Test architecture
arch:
	@echo "Test architecture"
	docker compose exec -it app vendor/bin/deptrac analyse --report-uncovered

clear:
	@echo "Clear all caches"
	docker compose exec -it app composer clear

## Clear application cache
cache-clear:
	@echo "Clear cache"
	docker compose exec -it app bin/console cache:clear

## Clear cache pool
cache-pool-clear:
	@echo "Clear pool cache"
	docker compose exec -it app bin/console cache:pool:clear --all

## Check for outdated dependencies
maintenance: maintain

## Update dependencies
maintain: show update-composer update-npm

## Show outdated composer dependencies
show:
	@echo "Show wether composer dependencies are outdated"
	docker compose exec -it app composer show --outdated
	
## Update composer dependencies
update-composer:
	@echo "Update Composer dependencies"
	docker compose exec -it app composer update -W

## Update NPM dependencies
update-npm:
	@echo "Update NPM dependencies"
	docker compose exec -it frontend npm update --save

## Generate test coverage report
coverage:
	@echo "Generate coverage report"
	docker compose exec -it app bin/phpunit -c phpunit.xml.dist --coverage-html ./coverage

## Open shell in frontend container
frontend-shell:
	@echo "Open shell on frontend container"
	docker compose exec -it frontend sh

## Open application in browser
open:
	@if command -v xdg-open > /dev/null 2>&1; then \
		xdg-open http://localhost:8000 2>/dev/null & \
	elif command -v open > /dev/null 2>&1; then \
		open http://localhost:8000; \
	elif command -v wslview > /dev/null 2>&1; then \
		wslview http://localhost:8000; \
	elif command -v cmd.exe > /dev/null 2>&1; then \
		cmd.exe /c start http://localhost:8000; \
	else \
		echo "❌ Could not detect browser launcher."; \
		echo "📍 Please open http://localhost:8000 manually"; \
	fi

## Show available targets
help:
	@echo "Available targets:"
	@awk '/^## / {desc=$$0; sub(/^## /, "", desc); getline; if(match($$0, /^([a-zA-Z0-9_-]+):/)) {printf "  %-20s %s\n", substr($$0, RSTART, RLENGTH-1), desc}}' $(MAKEFILE_LIST)

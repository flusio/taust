.DEFAULT_GOAL := help

USER = $(shell id -u):$(shell id -g)

ifdef NODOCKER
	PHP = php
	COMPOSER = composer
else
	PHP = ./docker/bin/php
	COMPOSER = ./docker/bin/composer
endif

.PHONY: docker-start
docker-start: .env ## Start a development server
	@echo "Running webserver on http://localhost:8000"
	docker compose -p taust -f docker/docker-compose.yml up

.PHONY: docker-build
docker-build: ## Rebuild the Docker image
	docker compose -p taust -f docker/docker-compose.yml build

.PHONY: docker-clean
docker-clean: ## Clean the Docker stuff
	docker compose -p taust -f docker/docker-compose.yml down

.PHONY: install
install: ## Install the dependencies
	$(COMPOSER) install

.PHONY: setup
setup: .env ## Setup the application system
	$(PHP) cli migrations setup --seed

.PHONY: lint
lint: ## Run the linter on the PHP files
	$(PHP) ./vendor/bin/phpstan analyse --memory-limit 1G -c phpstan.neon
	$(PHP) ./vendor/bin/phpcs --standard=PSR12 ./src

.PHONY: help
help:
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.env:
	@cp env.sample .env

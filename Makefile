.DEFAULT_GOAL := help

USER = $(shell id -u):$(shell id -g)

DOCKER_COMPOSE = docker compose -f docker/development/docker-compose.yml

ifdef NODOCKER
	PHP = php
	COMPOSER = composer
else
	PHP = ./docker/bin/php
	COMPOSER = ./docker/bin/composer
endif

.PHONY: docker-start
docker-start: PORT ?= 8000
docker-start: .env ## Start a development server (can take a PORT argument)
	@echo "Running webserver on http://localhost:$(PORT)"
	$(DOCKER_COMPOSE) up

.PHONY: docker-build
docker-build: ## Rebuild the Docker image
	$(DOCKER_COMPOSE) build --pull

.PHONY: docker-pull
docker-pull: ## Pull the Docker images from the Docker Hub
	$(DOCKER_COMPOSE) pull --ignore-buildable

.PHONY: docker-clean
docker-clean: ## Clean the Docker stuff
	$(DOCKER_COMPOSE) down -v

.PHONY: install
install: ## Install the dependencies
	$(COMPOSER) install

.PHONY: db-setup
db-setup: .env ## Setup the application system
	$(PHP) cli migrations setup --seed

.PHONY: db-rollback
db-rollback: ## Reverse the last migration (can take a STEPS argument)
ifdef STEPS
	$(CLI) migrations rollback --steps=$(STEPS)
else
	$(CLI) migrations rollback
endif

.PHONY: lint
lint: LINTER ?= all
lint: ## Run the linter on the PHP files (can take a LINTER argument)
ifeq ($(LINTER),$(filter $(LINTER), all phpstan))
	$(PHP) ./vendor/bin/phpstan analyse --memory-limit 1G -c phpstan.neon
endif
ifeq ($(LINTER),$(filter $(LINTER), all phpcs))
	$(PHP) ./vendor/bin/phpcs --standard=PSR12 ./src
endif

.PHONY: help
help:
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.env:
	@cp env.sample .env

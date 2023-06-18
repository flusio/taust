.DEFAULT_GOAL := help

USER = $(shell id -u):$(shell id -g)

ifdef NO_DOCKER
	PHP = php
	CLI = php cli
else
	PHP = ./docker/bin/php
	CLI = ./docker/bin/cli
endif

.PHONY: docker-start
docker-start: .env ## Start a development server
	@echo "Running webserver on http://localhost:8000"
	docker-compose -p taust -f docker/docker-compose.yml up

.PHONY: docker-build
docker-build: ## Rebuild the Docker image
	docker-compose -p taust -f docker/docker-compose.yml build

.PHONY: docker-clean
docker-clean: ## Clean the Docker stuff
	docker-compose -p taust -f docker/docker-compose.yml down

.PHONY: setup
setup: .env ## Setup the application system
	$(CLI) system setup

.PHONY: help
help:
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.env:
	@cp env.sample .env

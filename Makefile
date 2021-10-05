.DEFAULT_GOAL := help

USER = $(shell id -u):$(shell id -g)

ifdef NO_DOCKER
	PHP = php
	CLI = php cli
else
	PHP = ./docker/bin/php
	CLI = ./docker/bin/cli
endif

.PHONY: start
start: .env ## Start a development server (use Docker)
	@echo "Running webserver on http://localhost:8000"
	docker-compose -p taust -f docker/docker-compose.yml up

.PHONY: stop
stop: ## Stop and clean Docker server
	docker-compose -p taust -f docker/docker-compose.yml down

.PHONY: setup
setup: .env ## Setup the application system
	$(CLI) system setup

.PHONY: update
update: setup ## Update the application

.PHONY: reset
reset: ## Reset the database
	rm data/migrations_version.txt
	$(CLI) system setup

user: ## Create a user
	$(CLI) users create --username=alice --password=mysecret > /dev/null
	@echo "User alice (password: mysecret) created"

.PHONY: help
help:
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.env:
	@cp env.sample .env

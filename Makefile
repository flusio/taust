.DEFAULT_GOAL := help

USER = $(shell id -u):$(shell id -g)

ifdef NO_DOCKER
	PHP = php
else
	PHP = ./docker/bin/php
endif

.PHONY: start
start: ## Start a development server (use Docker)
	@echo "Running webserver on http://localhost:8000"
	docker-compose -f docker/docker-compose.yml up

.PHONY: stop
stop: ## Stop and clean Docker server
	docker-compose -f docker/docker-compose.yml down

.PHONY: setup
setup: .env ## Setup the application system
	$(PHP) ./cli --request /system/setup

.PHONY: update
update: setup ## Update the application

.PHONY: reset
reset: ## Reset the database
	rm data/migrations_version.txt
	$(PHP) ./cli --request /system/setup

user: ## Create a user
	$(PHP) ./cli --request /users/create -pusername=alice -ppassword=mysecret > /dev/null
	@echo "User alice (password: mysecret) created"

.PHONY: help
help:
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.env:
	@cp env.sample .env

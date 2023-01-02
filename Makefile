# Docker files
DOCKER_COMP_FILE 			= docker-compose.yml
DOCKER_COMP_FILE_OVERRIDE 	= docker-compose.override.yml
DOCKER_COMP_FILE_PROD 		= docker-compose.prod.yml

# Executables (local)
HAS_DOCKER_COMP_PLUGIN := $(shell docker compose version 2> /dev/null)
ifdef HAS_DOCKER_COMP_PLUGIN
	DOCKER_COMP_BASE = docker compose
else
	DOCKER_COMP_BASE = docker-compose
endif

DOCKER_COMP = $(DOCKER_COMP_BASE)
DOCKER_COMP_PROD = $(DOCKER_COMP_BASE) -f $(DOCKER_COMP_FILE) -f $(DOCKER_COMP_FILE_PROD)

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console
PHPUNIT  = $(PHP_CONT) php bin/phpunit

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————

build: ## Builds the Docker images
	$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	$(DOCKER_COMP) up --remove-orphans --detach

start: build up ## Build and up the containers

down: ## Stop the containers
	$(DOCKER_COMP) down --remove-orphans

stop_all: ## Stop all projects running containers without removing them
	docker stop $$(docker ps -a -q)

logs: ## Show live logs
	$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	$(PHP_CONT) sh

## —— Test 🧪 ——————————————————————————————————————————————————————————————

test: ## Run PHPUnit
	$(PHPUNIT)

## —— Composer 🧙 ——————————————————————————————————————————————————————————————

composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————

sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Troubleshooting 😵‍️ ———————————————————————————————————————————————————————

# See https://github.com/dunglas/symfony-docker/blob/main/docs/troubleshooting.md
permissions: ## Run it if you work on linux and cannot edit some of the project files
	$(DOCKER_COMP) run --rm php chown -R $$(id -u):$$(id -g) .

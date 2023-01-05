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

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc

## — 🎵 🐳 THE SYMFONY DOCKER MAKEFILE 🐳 🎵 ——————————————————————————————————

help: ## Print self-documented Makefile
	@grep -E '(^[.a-zA-Z_-]+[^:]+:.*##.*?$$)|(^#{2})' $(MAKEFILE_LIST) \
	| awk 'BEGIN {FS = "## "}; \
		{ \
			split($$1, command, ":"); \
			target=command[1]; \
			description=$$2; \
			# --- space --- \
			if (target=="##") \
				printf "\033[33m%s\n", ""; \
			# --- title --- \
			else if (target=="" && description!="") \
				printf "\033[33m\n%s\n", description; \
			# --- command + description --- \
			else if (target!="" && description!="") \
				printf "\033[32m  %-30s \033[0m%s\n", target, description; \
			# --- do nothing --- \
			else \
				; \
		}'
	@echo

## — DOCKER 🐳 ————————————————————————————————————————————————————————————————

build: ## Build or rebuild services
	$(DOCKER_COMP) build --pull --no-cache

up: ## Create and start containers
	$(DOCKER_COMP) up --remove-orphans

detach: ## Create and start containers in detached mode (no logs)
	$(DOCKER_COMP) up --remove-orphans --detach

start: build up ## Build, create and start containers

down: ## Stop and remove containers, networks
	$(DOCKER_COMP) down --remove-orphans

stop_all: ## Stop all projects running containers without removing them
	docker stop $$(docker ps -a -q)

logs: ## Show live logs
	$(DOCKER_COMP) logs --tail=0 --follow

## — PHP 🚀 ———————————————————————————————————————————————————————————————————

php: ## Run PHP, pass the parameter "c=" to run a given command, example: make composer c=bin/console
	@$(eval c ?=)
	$(PHP) $(c)

sh: ## Connect to the PHP FPM container
	$(PHP_CONT) sh

## — COMPOSER 🧙 ——————————————————————————————————————————————————————————————

composer: ## Run composer. Pass the parameter "c=" to run a given command (example: make composer c=req symfony/orm-pack)
	@$(eval c ?=)
	$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## — SYMFONY 🎵 ———————————————————————————————————————————————————————————————

sf: ## List all Symfony commands or pass the parameter "c=" to run a given command (example: make sf c=about)
	@$(eval c ?=)
	$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## — DOCTRINE 💽 ——————————————————————————————————————————————————————————————

db_create: ## Create the configured database
	$(SYMFONY) doctrine:database:create --if-not-exists

db_drop: ## Drop the configured database
	$(SYMFONY) doctrine:database:drop --if-exists --force

db_reset: db_drop db_create migration_run ## Drop & Create the configured database & Execute a migration

##

migration_new: ## Make a new migration
	$(SYMFONY) make:migration

migration_diff: ## Generate a migration by comparing your current database to your mapping information
	$(SYMFONY) doctrine:migrations:diff

migration_run: ## Execute a migration to the latest available version
	$(SYMFONY) doctrine:migrations:migrate

migration_update: migration_new migration_run ## Make a new migration and run it

##

schema_validate: ## Validate the mappings
	$(SYMFONY) doctrine:schema:validate

mapping_info: ## List mapped entities
	$(SYMFONY) doctrine:mapping:info

fixtures_load: ## Load fixtures
	$(SYMFONY) doctrine:fixtures:load

## — TEST & QUALITY ✅ ————————————————————————————————————————————————————————

test: ## Run PHPUnit
	$(PHP) bin/phpunit

phpcs: ## Run PHP CS (PHP_CodeSniffer) on `src` folder by default. Pass the parameter "c=" to run a given command (example: make phpcs c=src/Kernel.php)
	@$(eval c ?= src)
	$(PHP_CONT) ./vendor/bin/phpcs $(c)

phpcbf: ## Run PHP CS Fixer (PHP_CodeSniffer) on `src` folder by default. Pass the parameter "c=" to run a given command (example: make phpcbf c=src/Kernel.php)
	@$(eval c ?= src)
	$(PHP_CONT) ./vendor/bin/phpcbf $(c)

phpmd: ## Run PHP Mess Detector on `src` folder by default. Pass the parameter "c=" to run a given command (example: make phpcs c=src/Kernel.php)
	@$(eval c ?= src)
	$(PHP_CONT) ./vendor/bin/phpmd $(c) ansi cleancode,codesize,controversial,design,naming,unusedcode

## — TROUBLESHOOTING 😵‍️ ———————————————————————————————————————————————————————

permissions: ## Run it if you cannot edit some of the project files on Linux (https://github.com/dunglas/symfony-docker/blob/main/docs/troubleshooting.md)
	$(DOCKER_COMP) run --rm php chown -R $$(id -u):$$(id -g) .

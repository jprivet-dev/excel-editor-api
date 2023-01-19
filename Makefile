# Docker files
DOCKER_COMP_FILE 			= docker-compose.yml
DOCKER_COMP_FILE_OVERRIDE 	= docker-compose.override.yml
DOCKER_COMP_FILE_PROD 		= docker-compose.prod.yml
FOLDERS						= bin,config,public,src

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

## — 🎵 🐳 THE SYMFONY DOCKER MAKEFILE 🐳 🎵 ——————————————————————————————————

.DEFAULT_GOAL = help
.PHONY: help
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

.PHONY: build
build: ## Build the first time or rebuild fresh images if necessary
	$(DOCKER_COMP) build --pull --no-cache

.PHONY: up
up: ## Create and start containers
	$(DOCKER_COMP) up --remove-orphans

#.PHONY: up_prod
#up_prod: ## Create and start containers (PRODUCTION environement)
#	$(DOCKER_COMP) -f $(DOCKER_COMP_FILE) -f $(DOCKER_COMP_FILE_PROD) up

.PHONY: detach
detach: ## Create and start containers in detached mode (no logs)
	$(DOCKER_COMP) up --remove-orphans --detach

.PHONY: down
down: ## Stop and remove containers, networks
	$(DOCKER_COMP) down --remove-orphans

.PHONY: logs
logs: ## Show live logs
	$(DOCKER_COMP) logs --tail=0 --follow

##

.PHONY: install
install: build up ## Build & Start

.PHONY: start
start: up ## 'up' alias

#.PHONY: start_prod
#start_prod: up_prod ## up_prod alias

.PHONY: stop
stop: down ## 'down' alias

.PHONY: stop_all
stop_all: ## Stop all projects running containers without removing them
	docker stop $$(docker ps -a -q)

## — PHP 🚀 ———————————————————————————————————————————————————————————————————

.PHONY: php
php: ## Run PHP, pass the parameter "c=" to run a given command, example: make composer c=bin/console
	@$(eval c ?=)
	$(PHP) $(c)

.PHONY: sh
sh: ## Connect to the PHP FPM container
	$(PHP_CONT) sh

## — COMPOSER 🧙 ——————————————————————————————————————————————————————————————

.PHONY: composer
composer: ## Run composer. Pass the parameter "c=" to run a given command (example: make composer c=req symfony/orm-pack)
	@$(eval c ?=)
	$(COMPOSER) $(c)

.PHONY: vendor
vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## — SYMFONY 🎵 ———————————————————————————————————————————————————————————————

.PHONY: sf
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command (example: make sf c=about)
	@$(eval c ?=)
	$(SYMFONY) $(c)

.PHONY: cc
cc: c=c:c ## Clear the cache
cc: sf

## — DOCTRINE 💽 ——————————————————————————————————————————————————————————————

.PHONY: db_create
db_create: ## Create the configured database
	$(SYMFONY) doctrine:database:create --if-not-exists

.PHONY: db_drop
db_drop: ## Drop the configured database
	$(SYMFONY) doctrine:database:drop --if-exists --force

.PHONY: db_reset
db_reset: db_drop db_create migration_run ## Drop & Create the configured database & Execute a migration

##

.PHONY: migration_new
migration_new: ## Make a new migration
	$(SYMFONY) make:migration

.PHONY: migration_diff
migration_diff: ## Generate a migration by comparing your current database to your mapping information
	$(SYMFONY) doctrine:migrations:diff

.PHONY: migration_run
migration_run: ## Execute a migration to the latest available version
	$(SYMFONY) doctrine:migrations:migrate

.PHONY: migration_update
migration_update: migration_new migration_run ## Make a new migration and run it

##

.PHONY: schema_validate
schema_validate: ## Validate the mappings
	$(SYMFONY) doctrine:schema:validate

.PHONY: mapping_info
mapping_info: ## List mapped entities
	$(SYMFONY) doctrine:mapping:info

.PHONY: fixtures
fixtures: ## Load fixtures
	$(SYMFONY) doctrine:fixtures:load

## — TEST & QUALITY ✅ ————————————————————————————————————————————————————————

.PHONY: test
test: ## Run PHPUnit. Pass the parameter "c=" to run a given command (example: make test c=tests/Service/DataImportServiceTest.php)
	@$(eval c ?=)
	$(PHP) bin/phpunit $(c)

.PHONY: phpcs
phpcs: ## Run PHP CS (PHP_CodeSniffer). Pass the parameter "c=" to run a given command (example: make phpcs c=src/Kernel.php)
	@$(eval c ?=)
	$(PHP_CONT) ./vendor/bin/phpcs $(c)

.PHONY: phpcbf
phpcbf: ## Run PHP CS Fixer (PHP_CodeSniffer). Pass the parameter "c=" to run a given command (example: make phpcbf c=src/Kernel.php)
	@$(eval c ?=)
	$(PHP_CONT) ./vendor/bin/phpcbf $(c)

.PHONY: phpmd
phpmd: ## Run PHP Mess Detector on `src` folder by default. Pass the parameter "c=" to run a given command (example: make phpcs c=src/Kernel.php)
	@$(eval c ?= $(FOLDERS))
	$(PHP_CONT) ./vendor/bin/phpmd $(c) ansi phpmd.xml

## — JWT & OPENSSL 🔒️ —————————————————————————————————————————————————————————

.PHONY: openssl_version
openssl_version: ## Get the OpenSSL version
	$(PHP_CONT) openssl version

.PHONY: generate_keypair
generate_keypair: ## Generate the SSL keys (private.pem & public.pem) with lexik, for the JWT authentication
	$(SYMFONY) lexik:jwt:generate-keypair

## — TROUBLESHOOTING 😵‍️ ———————————————————————————————————————————————————————

.PHONY: permissions
permissions: ## Run it if you cannot edit some of the project files on Linux (https://github.com/dunglas/symfony-docker/blob/main/docs/troubleshooting.md)
	$(DOCKER_COMP) run --rm php chown -R $$(id -u):$$(id -g) .

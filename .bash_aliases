#!/usr/bin/env bash

# Load aliases:
# $ . .bash_aliases

HAS_DOCKER_COMP_PLUGIN=$(docker compose version 2> /dev/null)

if [ "${HAS_DOCKER_COMP_PLUGIN}" != "" ]; then
	DOCKER_COMP_BASE="docker compose"
else
	DOCKER_COMP_BASE="docker-compose"
fi

alias php="$DOCKER_COMP_BASE exec php php"
alias composer="$DOCKER_COMP_BASE exec php composer"
alias symfony="$DOCKER_COMP_BASE exec php php bin/console"
alias sf="symfony"
alias release=". scripts/release.sh"

echo -e '\033[1;42m Aliases loaded: php, composer, symfony, sf, release \033[0m'

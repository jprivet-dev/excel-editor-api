#!/usr/bin/env bash

# Usage:
# $ . scripts/release.sh

function bump_version_from_to() {
    local F_RESET="\033[0m"
    local C_BLUE="\033[34m"
    local C_GREEN="\033[32m"
    local C_YELLOW="\033[33m"

    while true; do
        echo -e -n "${C_BLUE}>${F_RESET} Previous release (e.g.: 1.0.0)? "

        exec </dev/tty
        read from

        if [ "${from}" != "" ]; then
            break
        fi
    done

    while true; do
        echo -e -n "${C_BLUE}>${F_RESET} Next release     (e.g.: 1.1.0)? "

        exec </dev/tty
        read to

        if [ "${to}" != "" ]; then
            break
        fi
    done

    from="v${from}"
    to="v${to}"

    local project="https://github.com/jprivet-dev/excel-editor-api"
    local branch_main="main"
    local branch_develop="develop"
    local branch_release="release/${to}"

    echo
    echo -e "${C_GREEN}#${F_RESET}"
    echo -e "${C_GREEN}# Bump release from ${from} to ${to}${F_RESET}"
    echo -e "${C_GREEN}#${F_RESET}"

    echo
    echo -e "${C_BLUE}1. CREATE NEW RELEASE BRANCH ———————————————————————————————————————————${F_RESET}"
    echo "   $ git switch ${branch_develop}"
    echo "   $ git pull origin ${branch_develop}"
    echo "   $ git switch -c ${branch_release}"

    echo
    echo -e "${C_BLUE}2. REPLACE THE VERSION —————————————————————————————————————————————————${F_RESET}"
    echo "   - Replace ${from} by ${to} in the files (README.adoc, composer.json, ...)"

    echo
    echo -e "${C_BLUE}3. SAVE THE FILES MODIFICATIONS ————————————————————————————————————————${F_RESET}"
    echo "   $ git add ."
    echo "   $ git commit -m \"release: bump version to ${to}\""
    echo "   $ git push origin ${branch_release}"

    echo
    echo -e "${C_BLUE}4. CREATE THE PULL REQUEST ON DEVELOP ——————————————————————————————————${F_RESET}"
    echo "   - Go on      : ${project}/compare/${branch_develop}...${branch_release}"
    echo -e "   - Title      : ${C_YELLOW}Release ${to}${F_RESET}"
    echo "   - Description: Empty"
    echo "   - Click on the button \"Merge the pull request\""
    echo

    while true; do
        echo -e -n "${C_BLUE}>${F_RESET} What is the PR id (e.g.: 210)? "

        exec </dev/tty
        read pr_id

        if [ "${pr_id}" != "" ]; then
            break
        fi
    done

    echo
    echo -e "${C_BLUE}5. TAG THE MERGE COMMIT ON DEVELOP —————————————————————————————————————${F_RESET}"
    echo "   - Go on        : ${project}/releases/new"
    echo -e "   - Tag version  : ${C_YELLOW}${to}${F_RESET}"
    echo "   - Target       : In \"Recent Commits\", choose the last merge on ${branch_develop} branch"
    echo -e "   - Release title: ${C_YELLOW}${to}${F_RESET}"
    echo "   - Describe this release (copy/past the following text):"
    echo
    echo -e "${C_YELLOW}Release ${to}${F_RESET}"
    echo -e "${C_YELLOW}PR #${pr_id}${F_RESET}"
    echo -e "${C_YELLOW}Compare on ${project}/compare/${from}...${to}${F_RESET}"
    echo
    echo "   - Set as the latest release"
    echo "   - Click on the button \"Publish release\""

    echo
    echo -e "${C_BLUE}6. DEPLOY ON MAIN ——————————————————————————————————————————————————————${F_RESET}"
    echo "   - Go on      : ${project}/compare/${branch_main}...${branch_develop}"
    echo -e "   - Title      : ${C_YELLOW}Deployment${F_RESET}"
    echo "   - Description: Empty"
    echo "   - Click on the button \"Merge the pull request\""

    echo
}

bump_version_from_to "$@"

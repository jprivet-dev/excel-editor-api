#!/usr/bin/env bash

# Usage:
# $ . scripts/release.sh

function bump_version_from_to() {

	local F_RESET="\033[0m"
	local C_BLUE="\033[34m"
	local C_GREEN="\033[32m"
	local C_YELLOW="\033[33m"
	local C_LIGHT_YELLOW="\033[93m"
	local SPACE="+"
	local DASH="%23"
	local NEW_LINE="%0A"
	local release_title="Release"
	local prerelease=0
	local last_tag=$(git describe --tags --abbrev=0)

	function replace_first_occurrence() {
    	local from=$1
    	local to=$2
    	local file=$3
    	echo
    	echo "#"
    	echo "# Bump version to ${to} in ${file} file"
    	echo "#"
    	echo
    	sed -i "0,/${from}/{s/${from}/${to}/}" "${file}"
    	git diff "${file}"
    	echo
    }

	while true; do
		echo -e -n "${C_GREEN}> Previous release: ${C_LIGHT_YELLOW}[${last_tag}]${F_RESET} "

		exec </dev/tty
		read from

		if [ "${from}" == "" ]; then
			from="${last_tag}"
		fi

		if [ "${from}" != "" ]; then
			break
		fi
	done

	while true; do
		echo -e -n "${C_GREEN}> Next release:${F_RESET} "

		exec </dev/tty
		read to

		if [ "${to}" != "" ]; then
			break
		fi
	done

	while true; do
		echo -e -n "${C_GREEN}> As pre-release? (yes/no) ${C_LIGHT_YELLOW}[no]${F_RESET} "

		exec </dev/tty
		read prerelease_choice

		if [ "${prerelease_choice}" == "yes" -o "${prerelease_choice}" == "y" -o "${prerelease_choice}" == "no" -o "${prerelease_choice}" == "n" -o "${prerelease_choice}" == "" ]; then
			break
		fi
	done

	if [ "${prerelease_choice}" == "" ]; then
		prerelease_choice="no"
	fi

	if [ "${prerelease_choice}" == "yes" -o "${prerelease_choice}" == "y" ]; then
		release_title="Pre-release"
		prerelease=1
	fi

	local project="https://github.com/jprivet-dev/excel-editor-api"
	local branch_main="main"
	local branch_develop="develop"
	local branch_release="release/${to}"

	echo
	echo -e "${C_BLUE}#${F_RESET}"
	echo -e "${C_BLUE}# ${release_title}${F_RESET}"
	echo -e "${C_BLUE}# From: ${C_LIGHT_YELLOW}${from}${F_RESET}"
	echo -e "${C_BLUE}# To  : ${C_LIGHT_YELLOW}${to}${F_RESET}"
	echo -e "${C_BLUE}#${F_RESET}"

	echo
	echo -e "${C_BLUE}1. CREATE NEW RELEASE BRANCH ———————————————————————————————————————————${F_RESET}"
	echo
	echo "$ git switch ${branch_develop}"
	echo "$ git pull origin ${branch_develop}"
	echo "$ git switch -c ${branch_release}"

	echo
	echo -e "${C_BLUE}2. REPLACE THE VERSION —————————————————————————————————————————————————${F_RESET}"
	echo

	while true; do
		echo -e -n "${C_GREEN}> Replace ${from} by ${to} in the files (README.adoc, composer.json)? (yes/no) ${C_LIGHT_YELLOW}[yes]${F_RESET} "

		exec </dev/tty
		read replace_choice

		if [ "${replace_choice}" == "" ]; then
			replace_choice="yes"
		fi

		if [ "${replace_choice}" == "yes" -o "${replace_choice}" == "y" -o "${replace_choice}" == "no" -o "${replace_choice}" == "n" -o "${replace_choice}" == "" ]; then
			break
		fi
	done

	if [ "${replace_choice}" == "yes" -o "${replace_choice}" == "y" ]; then
		replace_first_occurrence "${from}" "${to}" README.adoc
		replace_first_occurrence "${from}" "${to}" composer.json
	fi

	echo
	echo -e "${C_BLUE}3. SAVE THE FILES MODIFICATIONS ————————————————————————————————————————${F_RESET}"
	echo

	echo "$ git add ."
	echo "$ git commit -m \"release: bump version to ${to}\""
	echo "$ git push origin ${branch_release}"

	local new_pr_url="${project}/compare/${branch_develop}...${branch_release}"
	new_pr_url+="?quick_pull=1"
	new_pr_url+="&title=${release_title}${SPACE}${to}"

	echo
	echo -e "${C_BLUE}4. CREATE THE PULL REQUEST ON DEVELOP ——————————————————————————————————${F_RESET}"
	echo

	echo "- Go on      : ${new_pr_url}"
	echo "- Click on the button \"Create pull request\""
	echo -e "- Title      : ${C_YELLOW}${release_title} ${to}${F_RESET}"
	echo "- Description: Empty"
	echo "- Click on the button \"Merge the pull request\""
	echo

	while true; do
		echo -e -n "${C_GREEN}> What is the PR id? (e.g.: 210)${F_RESET} "

		exec </dev/tty
		read pr_id

		if [ "${pr_id}" != "" ]; then
			break
		fi
	done

	local new_tag_url="${project}/releases/new"
	new_tag_url+="?tag=${to}"
	new_tag_url+="&target=${branch_develop}"
	new_tag_url+="&title=${to}"
	new_tag_url+="&body=${DASH}${DASH}${SPACE}${release_title}${SPACE}${to}${NEW_LINE}**Pull${SPACE}Request**:${SPACE}${DASH}${pr_id}${NEW_LINE}**Full${SPACE}Changelog**:${SPACE}${project}/compare/${from}...${to}"

	if [ "${prerelease}" == 1 ]; then
		new_tag_url+="&prerelease=1"
	fi

	echo
	echo -e "${C_BLUE}5. TAG THE MERGE COMMIT ON DEVELOP —————————————————————————————————————${F_RESET}"
	echo

	echo "- Go on        : ${new_tag_url}"
	echo -e "- Tag version  : ${C_YELLOW}${to}${F_RESET}"
	echo "- Target       : In \"Recent Commits\", choose the last merge on ${branch_develop} branch"
	echo -e "- Release title: ${C_YELLOW}${to}${F_RESET}"
	echo "- Describe this release (copy/past the following text):"
	echo
	echo -e "${C_YELLOW}## ${release_title} ${to}${F_RESET}"
	echo -e "${C_YELLOW}**Pull Request**: #${pr_id}${F_RESET}"
	echo -e "${C_YELLOW}**Full Changelog**: ${project}/compare/${from}...${to}${F_RESET}"
	echo
	#echo "- Set as the latest release"

	if [ "${prerelease}" == 1 ]; then
		echo "- Set as a pre-release"
	fi

	echo "- Click on the button \"Publish release\""

	if [ "${prerelease}" == 0 ]; then
		echo
		echo -e "${C_BLUE}6. DEPLOY ON MAIN ——————————————————————————————————————————————————————${F_RESET}"
		echo

		echo "- Go on      : ${project}/compare/${branch_main}...${branch_develop}"
		echo "- Click on the button \"Create pull request\""
		echo -e "- Title      : ${C_YELLOW}Deployment${F_RESET}"
		echo "- Description: Empty"
		echo "- Click on the button \"Merge the pull request\""
	fi

	echo
}

bump_version_from_to "$@"

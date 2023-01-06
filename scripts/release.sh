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
	local step=0

	function bump_version_in_file() {
		local from=$1
		local to=$2
		local file=$3

		echo
		echo "#"
		echo "# Bump version to ${to} in ${file} file"
		echo "#"
		echo

		# Replace only first occurrence
		sed -i "0,/${from}/{s/${from}/${to}/}" "${file}"
		git diff "${file}"
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

	((step++))
	echo
	echo -e "${C_BLUE}${step}. CREATE NEW RELEASE BRANCH ———————————————————————————————————————————${F_RESET}"
	echo
	echo "$ git fetch origin ${branch_develop}"
	echo "$ git checkout -b ${branch_release} origin/${branch_develop}"

	while true; do
		echo -e -n "${C_GREEN}> Run the above git commands? (yes/no) ${C_LIGHT_YELLOW}[yes]${F_RESET} "

		exec </dev/tty
		read new_release_run_commands

		if [ "${new_release_run_commands}" == "" ]; then
			new_release_run_commands="yes"
		fi

		if [ "${new_release_run_commands}" == "yes" -o "${new_release_run_commands}" == "y" -o "${new_release_run_commands}" == "no" -o "${new_release_run_commands}" == "n" -o "${new_release_run_commands}" == "" ]; then
			break
		fi
	done

	if [ "${new_release_run_commands}" == "yes" -o "${new_release_run_commands}" == "y" ]; then
		git fetch origin "${branch_develop}" && \
		git checkout -b "${branch_release}" origin/"${branch_develop}"
	fi

	((step++))
	echo
	echo -e "${C_BLUE}${step}. REPLACE THE VERSION —————————————————————————————————————————————————${F_RESET}"
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
		bump_version_in_file "${from}" "${to}" README.adoc
		bump_version_in_file "${from}" "${to}" composer.json
	fi

	((step++))
	echo
	echo -e "${C_BLUE}${step}. SAVE THE FILES MODIFICATIONS ————————————————————————————————————————${F_RESET}"
	echo

	echo "$ git add ."
	echo "$ git commit -m \"release: bump version to ${to}\""
	echo "$ git push origin ${branch_release}"

	while true; do
		echo -e -n "${C_GREEN}> Run the above git commands? (yes/no) ${C_LIGHT_YELLOW}[yes]${F_RESET} "

		exec </dev/tty
		read save_files_run_commands

		if [ "${save_files_run_commands}" == "" ]; then
			save_files_run_commands="yes"
		fi

		if [ "${save_files_run_commands}" == "yes" -o "${save_files_run_commands}" == "y" -o "${save_files_run_commands}" == "no" -o "${save_files_run_commands}" == "n" -o "${save_files_run_commands}" == "" ]; then
			break
		fi
	done

	if [ "${save_files_run_commands}" == "yes" -o "${save_files_run_commands}" == "y" ]; then
		git add . && \
		git commit -m "release: bump version to ${to}" && \
		git push origin "${branch_release}"
	fi

	local new_pr_release_url="${project}/compare/${branch_develop}...${branch_release}"
	new_pr_release_url+="?quick_pull=1"
	new_pr_release_url+="&title=${release_title}${SPACE}${to}"

	((step++))
	echo
	echo -e "${C_BLUE}${step}. CREATE THE PULL REQUEST ON DEVELOP ——————————————————————————————————${F_RESET}"
	echo

	echo "- Go on      : ${new_pr_release_url}"
	echo "- Click on the button \"Create pull request\""
	echo -e "- Title      : ${C_YELLOW}${release_title} ${to}${F_RESET}"
	echo "- Description: Empty"
	echo "- Click on the button \"Merge the pull request\""

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

	((step++))
	echo
	echo -e "${C_BLUE}${step}. TAG THE MERGE COMMIT ON DEVELOP —————————————————————————————————————${F_RESET}"
	echo

	echo "- Go on        : ${new_tag_url}"
	echo -e "- Tag version  : ${C_YELLOW}${to}${F_RESET}"
	echo "- Target       : Choose the ${branch_develop} branch"
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
		((step++))
		echo
		echo -e "${C_BLUE}${step}. DEPLOY ON MAIN ——————————————————————————————————————————————————————${F_RESET}"
		echo

		local new_pr_deploy_url="${project}/compare/${branch_main}...${branch_develop}"
		new_pr_deploy_url+="?quick_pull=1"
		new_pr_deploy_url+="&title=Deployment"

		echo "- Go on      : ${new_pr_deploy_url}"
		echo "- Click on the button \"Create pull request\""
		echo -e "- Title      : ${C_YELLOW}Deployment${F_RESET}"
		echo "- Description: Empty"
		echo "- Click on the button \"Merge the pull request\""
	fi

	((step++))
	echo
	echo -e "${C_BLUE}${step}. CONTINUE THE JOB ON A NEXT BRANCH ———————————————————————————————————${F_RESET}"
	echo
	echo "$ git fetch origin ${branch_develop}"
	echo "$ git checkout -b ${to}-next origin/${branch_develop}"

	while true; do
		echo -e -n "${C_GREEN}> Run the above git commands? (yes/no) ${C_LIGHT_YELLOW}[yes]${F_RESET} "

		exec </dev/tty
		read next_branch_run_commands

		if [ "${next_branch_run_commands}" == "" ]; then
			next_branch_run_commands="yes"
		fi

		if [ "${next_branch_run_commands}" == "yes" -o "${next_branch_run_commands}" == "y" -o "${next_branch_run_commands}" == "no" -o "${next_branch_run_commands}" == "n" -o "${next_branch_run_commands}" == "" ]; then
			break
		fi
	done

	if [ "${next_branch_run_commands}" == "yes" -o "${next_branch_run_commands}" == "y" ]; then
		git fetch origin "${branch_develop}" && \
		git checkout -b "${to}"-next origin/"${branch_develop}"
	fi

	echo
}

bump_version_from_to "$@"

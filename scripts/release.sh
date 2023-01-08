#!/usr/bin/env bash

# Usage:
# $ . scripts/release.sh

function bump_version_from_to() {
	local SPLIT="——————————————————————————————————————————————————————"
	local F_RESET="\033[0m"
	local C_BLUE="\033[34m"
	local C_GREEN="\033[32m"
	local C_YELLOW="\033[33m"
	local C_LIGHT_YELLOW="\033[93m"
	local SPACE="+"
	local DASH="%23"
	local NEW_LINE="%0A"
	local BRANCH_RELEASE_PREFIX="release-stage"
	local BRANCH_RELEASE_NEXT_SUFFIX="release-stage"

	local release_title="Release"
	local prerelease=0
	local last_tag=$(git describe --tags --abbrev=0)
	local step=0

	local prompt_yes_no_choice

	function prompt_yes_no() {
		local YES="yes"
		local YES_SHORT="y"
		local NO="no"
		local NO_SHORT="n"

		local choice
		local label=$1
		local default=$2

		prompt_yes_no_choice=""

		if [ "${default}" == "${YES}" -o "${default}" == "${YES_SHORT}" ]; then
			default="${YES}"
		elif [ "${default}" == "${NO}" -o "${default}" == "${NO_SHORT}" ]; then
			default="${NO}"
		else
			default="${YES}"
		fi

		while true; do
			echo -e -n "${C_GREEN}> ${label}? (yes/no) ${C_LIGHT_YELLOW}[${default}]${F_RESET} "

			exec </dev/tty
			read choice

			if [ "${choice}" == "${YES}" -o "${choice}" == "${YES_SHORT}" -o "${choice}" == "${NO}" -o "${choice}" == "${NO_SHORT}" -o "${choice}" == "" ]; then
				break
			fi
		done

		if [ "${choice}" == "${YES}" -o "${choice}" == "${YES_SHORT}" ]; then
			choice="${YES}"
		elif [ "${choice}" == "${NO}" -o "${choice}" == "${NO_SHORT}" ]; then
			choice="${NO}"
		else
			choice="${default}"
		fi

		prompt_yes_no_choice="${choice}"
	}

	local prompt_text

	function prompt() {
		local label=$1
		local example=$2
		local default=$3

		local complete_label="${C_GREEN}> ${label}:"
		if [ "${example}" != "" ]; then
			complete_label+=" (e.g.: ${example})"
		fi
		if [ "${default}" != "" ]; then
			complete_label+=" ${C_LIGHT_YELLOW}[${default}]"
		fi
		complete_label+="${F_RESET} "

		while true; do
			echo -e -n "${complete_label}"

			exec </dev/tty
			read text

			if [ "${text}" == "" -a "${default}" != "" ]; then
				text="${default}"
			fi

			if [ "${text}" != "" ]; then
				prompt_text="${text}"
				break
			fi
		done
	}

	function replace_first_occurrence() {
		local from=$1
		local to=$2
		local file=$3

		echo
		echo "#"
		echo "# Replace ${from} by ${to} in ${file} file"
		echo "#"
		echo

		# Replace only first occurrence
		sed -i "0,/${from}/{s/${from}/${to}/}" "${file}"
		git diff "${file}"
	}

	prompt "Current release" "" "${last_tag}"
	local from="${prompt_text}"

	prompt "Next release" "v2.1.0"
	local to="${prompt_text}"

	prompt_yes_no "As pre-release" "no"
	local prerelease_choice="${prompt_yes_no_choice}"

	if [ "${prerelease_choice}" == "yes" ]; then
		release_title="Pre-release"
		prerelease=1
	fi

	local project="https://github.com/jprivet-dev/excel-editor-api"
	local branch_main="main"
	local branch_release="${BRANCH_RELEASE_PREFIX}-${to}"

	echo
	echo -e "${C_BLUE}#${F_RESET}"
	echo -e "${C_BLUE}# ${release_title}${F_RESET}"
	echo -e "${C_BLUE}# From: ${C_LIGHT_YELLOW}${from}${F_RESET}"
	echo -e "${C_BLUE}# To  : ${C_LIGHT_YELLOW}${to}${F_RESET}"
	echo -e "${C_BLUE}#${F_RESET}"

	((step++))
	echo
	echo -e "${C_BLUE}${SPLIT}${F_RESET}"
	echo -e "${C_BLUE}${step}. Create the branch '${branch_release}'${F_RESET}"
	echo
	echo "$ git fetch origin ${branch_main}"
	echo "$ git checkout -b ${branch_release} origin/${branch_main}"

	prompt_yes_no "Run the above git commands"
	local new_release_choice="${prompt_yes_no_choice}"

	if [ "${new_release_choice}" == "yes" ]; then
		git fetch origin "${branch_main}" &&
			git checkout -b "${branch_release}" origin/"${branch_main}"
	fi

	((step++))
	echo
	echo -e "${C_BLUE}${SPLIT}${F_RESET}"
	echo -e "${C_BLUE}${step}. Replace the version${F_RESET}"
	echo

	prompt_yes_no "Replace ${from} by ${to} in the files (README.adoc, composer.json)"
	local replace_choice="${prompt_yes_no_choice}"

	if [ "${replace_choice}" == "yes" ]; then
		replace_first_occurrence "${from}" "${to}" README.adoc
		replace_first_occurrence "${from}" "${to}" composer.json
	fi

	((step++))
	echo
	echo -e "${C_BLUE}${SPLIT}${F_RESET}"
	echo -e "${C_BLUE}${step}. Save the files modifications${F_RESET}"
	echo

	echo "$ git commit --all -m \"release: bump version to ${to}\""
	echo "$ git push origin ${branch_release}"

	prompt_yes_no "Run the above git commands"
	local save_files_choice="${prompt_yes_no_choice}"

	if [ "${save_files_choice}" == "yes" ]; then
		git commit --all -m "release: bump version to ${to}" &&
			git push origin "${branch_release}"
	fi

	local new_pr_release_url="${project}/compare/${branch_main}...${branch_release}"
	new_pr_release_url+="?quick_pull=1"
	new_pr_release_url+="&title=${release_title}${SPACE}${to}"

	((step++))
	echo
	echo -e "${C_BLUE}${SPLIT}${F_RESET}"
	echo -e "${C_BLUE}${step}. Create the pull request on main${F_RESET}"
	echo

	echo "- Go on      : ${new_pr_release_url}"
	echo "- Click on the button \"Create pull request\""
	echo -e "- Title      : ${C_YELLOW}${release_title} ${to}${F_RESET}"
	echo "- Description: Empty"
	echo "- Click on the button \"Merge the pull request\""

	prompt "PR id" "210"
	local pr_id="${prompt_text}"

	local new_tag_url="${project}/releases/new"
	new_tag_url+="?tag=${to}"
	new_tag_url+="&target=${branch_main}"
	new_tag_url+="&title=${to}"
	new_tag_url+="&body=${DASH}${DASH}${SPACE}${release_title}${SPACE}${to}${NEW_LINE}**Pull${SPACE}Request**:${SPACE}${DASH}${pr_id}${NEW_LINE}**Full${SPACE}Changelog**:${SPACE}${project}/compare/${from}...${to}"

	if [ "${prerelease}" == 1 ]; then
		new_tag_url+="&prerelease=1"
	fi

	((step++))
	echo
	echo -e "${C_BLUE}${SPLIT}${F_RESET}"
	echo -e "${C_BLUE}${step}. Tag the merge commit on main${F_RESET}"
	echo

	echo "- Go on        : ${new_tag_url}"
	echo -e "- Tag version  : ${C_YELLOW}${to}${F_RESET}"
	echo "- Target       : Choose the ${branch_main} branch"
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

	((step++))
	echo
	echo -e "${C_BLUE}${SPLIT}${F_RESET}"
	echo -e "${C_BLUE}${step}. Clean all & Continue the job on a next branch${F_RESET}"
	echo
	echo "$ git push origin --delete ${branch_release}"
	echo "$ git checkout ${branch_main} -f"
	echo "$ git branch -D ${branch_release}"
	echo "$ git pull --ff origin ${branch_main}"
	echo "$ git checkout -b ${to}-${BRANCH_RELEASE_NEXT_SUFFIX}"

	prompt_yes_no "Run the above git commands"
	local next_branch_choice="${prompt_yes_no_choice}"

	if [ "${next_branch_choice}" == "yes" ]; then
		git push origin --delete "${branch_release}" &&
			git checkout "${branch_main}" -f &&
			git branch -D "${branch_release}" &&
			git pull --ff origin "${branch_main}" &&
			git checkout -b "${to}"-"${BRANCH_RELEASE_NEXT_SUFFIX}"
	fi

	echo
}

bump_version_from_to "$@"

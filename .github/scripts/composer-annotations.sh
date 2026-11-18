#!/usr/bin/env bash
#
# Turn the output of a failed Composer check into GitHub annotations and a
# job summary, so the problem shows on the PR instead of only in the log.
#
# Usage: composer-annotations.sh <check> <output-file>
#
#   require-checker  <output-file> holds `composer require-checker --
#                    --output=json`. Each unknown symbol becomes an error on
#                    composer.json with the dependency the tool guessed.
#   validate         <output-file> holds `composer validate --strict` (stdout
#                    and stderr). Each "- " finding becomes an error on
#                    composer.json, or on composer.lock for lock-file findings.
#   normalize        <output-file> holds `composer normalize --dry-run`. The
#                    diff goes in the summary under one error on composer.json.
#                    Output that is not a diff means normalize itself failed.
#
# The caller runs the check and calls this only when it failed; the script
# reports and leaves the exit status to the caller. Annotations are printed
# only when GITHUB_ACTIONS is true, and the summary is appended only when
# GITHUB_STEP_SUMMARY is set, so the plain report stays readable elsewhere.
#
# Exit codes
#   0  report written
#   2  usage error

set -euo pipefail

usage() {
    printf 'Usage: %s require-checker|validate|normalize <output-file>\n' "${0##*/}"
}

# Escape a value for a workflow command, per the Actions toolkit rules.
escape_data() {
    local s=$1
    s=${s//'%'/%25}
    s=${s//$'\r'/%0D}
    s=${s//$'\n'/%0A}
    printf '%s' "${s}"
}

annotate() {
    local file=$1 title=$2 message=$3 escaped
    if [[ ${GITHUB_ACTIONS:-} = true ]]; then
        escaped=$(escape_data "${message}")
        printf '::error file=%s,title=%s::%s\n' "${file}" "${title}" "${escaped}"
    fi
}

summarize() {
    if [[ -n ${GITHUB_STEP_SUMMARY:-} ]]; then
        cat >> "${GITHUB_STEP_SUMMARY}"
    else
        cat > /dev/null
    fi
}

# Print stdin as a fenced block, with a fence longer than any backtick run
# inside it so the content cannot close the block early.
fenced() {
    local lang=$1 content fence='```'
    content=$(cat)
    while [[ ${content} == *"${fence}"* ]]; do
        fence+='`'
    done
    printf '%s%s\n%s\n%s\n' "${fence}" "${lang}" "${content}" "${fence}"
}

# Backticks in the single-quoted strings below are Markdown, not expansions.
# shellcheck disable=SC2016
report_require_checker() {
    local output_file=$1 rows
    if ! rows=$(jq -r '."unknown-symbols" | to_entries[] | "\(.key)\t\(.value | join(", "))"' "${output_file}" 2> /dev/null) || [[ -z ${rows} ]]; then
        # No symbols to report: the tool itself failed. Show what it printed.
        cat "${output_file}"
        annotate composer.json 'Composer Require Checker' 'Composer Require Checker did not produce a report; see the job log.'
        return 0
    fi

    local symbol guess message
    local -a table=()
    printf 'Symbols used in the code that no declared dependency provides:\n\n'
    while IFS=$'\t' read -r symbol guess; do
        message="${symbol} is used but no package or extension in composer.json provides it."
        if [[ -n ${guess} ]]; then
            message+=" Guessed dependency: ${guess}."
        fi
        printf '  %s%s\n' "${symbol}" "${guess:+ (guessed: ${guess})}"
        annotate composer.json 'Unknown symbol' "${message}"
        table+=("$(printf '| `%s` | %s |' "${symbol}" "${guess:-none}")")
    done <<< "${rows}"

    {
        printf '## Composer Require Checker: unknown symbols\n\n'
        printf 'Declare the dependency in `composer.json`, or, for a symbol that is not a dependency, add it to `.composer-require-checker.json`.\n\n'
        printf '| Symbol | Guessed dependency |\n| - | - |\n'
        printf '%s\n' "${table[@]}"
    } | summarize
}

report_validate() {
    local output_file=$1 line file=composer.json found=0
    cat "${output_file}"
    while IFS= read -r line; do
        case ${line} in
            '# Lock file'*) file=composer.lock ;;
            '# '*) file=composer.json ;;
            '- '*)
                annotate "${file}" 'composer validate' "${line#- }"
                found=1
                ;;
            *) ;;
        esac
    done < "${output_file}"
    if (( found == 0 )); then
        annotate composer.json 'composer validate' 'composer.json failed strict validation; see the job log.'
    fi

    {
        printf '## composer validate --strict failed\n\n'
        fenced text < "${output_file}"
    } | summarize
}

# shellcheck disable=SC2016
report_normalize() {
    local output_file=$1
    cat "${output_file}"
    if ! grep -qE '^(---|\+\+\+) ' "${output_file}"; then
        # No diff: normalize could not run (a missing plugin, a parse error).
        annotate composer.json 'composer normalize' 'composer normalize failed without a diff; see the job log.'
        {
            printf '## composer normalize failed\n\n'
            fenced text < "${output_file}"
        } | summarize
        return 0
    fi
    annotate composer.json 'composer normalize' 'composer.json is not normalized. Run `composer normalize` and commit the result.'

    {
        printf '## composer.json is not normalized\n\n'
        printf 'Run `composer normalize` and commit the result. The changes it would make:\n\n'
        fenced diff < "${output_file}"
    } | summarize
}

main() {
    if (( $# != 2 )); then
        usage >&2
        exit 2
    fi
    local check=$1 output_file=$2
    if [[ ! -r ${output_file} ]]; then
        printf '%s: cannot read %s\n' "${0##*/}" "${output_file}" >&2
        exit 2
    fi
    case ${check} in
        require-checker) report_require_checker "${output_file}" ;;
        validate) report_validate "${output_file}" ;;
        normalize) report_normalize "${output_file}" ;;
        *)
            usage >&2
            exit 2
            ;;
    esac
}

main "$@"

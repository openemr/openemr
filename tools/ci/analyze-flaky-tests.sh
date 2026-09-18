#!/usr/bin/env bash
#
# analyze-flaky-tests.sh - Analyze flaky CI test patterns
#
# Usage: ./analyze-flaky-tests.sh [--runs N] [--workflow NAME]
#
# Fetches recent CI runs from GitHub and analyzes:
# - Retry rates
# - Which jobs/configurations fail most often
# - Common failure patterns and error messages
#
# Requires: gh (GitHub CLI), jq

set -euo pipefail

readonly DEFAULT_RUNS=30
readonly DEFAULT_WORKFLOW='test-all.yml'
readonly REPO='openemr/openemr'

# Parse arguments
runs=${DEFAULT_RUNS}
workflow=${DEFAULT_WORKFLOW}
while (( $# > 0 )); do
    case $1 in
        --runs) runs=$2; shift 2 ;;
        --workflow) workflow=$2; shift 2 ;;
        *) echo "Unknown option: $1" >&2; exit 1 ;;
    esac
done

main() {
    echo "=== Flaky Test Analysis for ${REPO} ==="
    echo "Workflow: ${workflow}"
    echo "Analyzing last ${runs} runs on master branch"
    echo

    analyze_retry_rate
    echo
    analyze_failing_configurations
    echo
    analyze_failure_patterns
}

analyze_retry_rate() {
    echo "--- Retry Rate Statistics ---"

    local data
    data=$(gh run list --repo "${REPO}" --branch master --workflow "${workflow}" --limit "${runs}" \
        --json attempt,conclusion,databaseId,createdAt)

    local total retries rate
    total=$(echo "${data}" | jq 'length')
    retries=$(echo "${data}" | jq '[.[] | select(.attempt > 1)] | length')

    if (( total > 0 )); then
        rate=$(echo "scale=1; ${retries} * 100 / ${total}" | bc)
    else
        rate=0
    fi

    echo "Total runs: ${total}"
    echo "Runs needing retry: ${retries}"
    echo "Retry rate: ${rate}%"

    # Show attempt distribution
    echo
    echo "Attempt distribution:"
    echo "${data}" | jq -r 'group_by(.attempt) | .[] | "  Attempt \(.[0].attempt): \(length) runs"'
}

analyze_failing_configurations() {
    echo "--- Failing Configurations (from first attempts) ---"

    # Get run IDs that needed retries
    local run_ids
    run_ids=$(gh run list --repo "${REPO}" --branch master --workflow "${workflow}" --limit "${runs}" \
        --json databaseId,attempt | jq -r '.[] | select(.attempt > 1) | .databaseId')

    if [[ -z "${run_ids}" ]]; then
        echo "No retried runs found"
        return
    fi

    # Collect failed job names from first attempts
    local failed_jobs=()
    while IFS= read -r run_id; do
        [[ -z "${run_id}" ]] && continue
        local jobs
        jobs=$(gh api "repos/${REPO}/actions/runs/${run_id}/attempts/1/jobs" \
            --jq '.jobs[] | select(.conclusion == "failure") | .name' 2>/dev/null || true)
        while IFS= read -r job; do
            [[ -n "${job}" ]] && failed_jobs+=("${job}")
        done <<< "${jobs}"
    done <<< "${run_ids}"

    if (( ${#failed_jobs[@]} == 0 )); then
        echo "No failed jobs found"
        return
    fi

    # Count and sort
    printf '%s\n' "${failed_jobs[@]}" | sort | uniq -c | sort -rn | head -15
}

analyze_failure_patterns() {
    echo "--- Common Failure Patterns ---"

    # Get recent run IDs that needed retries
    local run_ids
    run_ids=$(gh run list --repo "${REPO}" --branch master --workflow "${workflow}" --limit "${runs}" \
        --json databaseId,attempt | jq -r '.[] | select(.attempt > 1) | .databaseId' | head -10)

    if [[ -z "${run_ids}" ]]; then
        echo "No retried runs found"
        return
    fi

    local temp_file
    temp_file=$(mktemp)

    while IFS= read -r run_id; do
        [[ -z "${run_id}" ]] && continue

        # Get job ID for failed job in first attempt
        local job_id
        job_id=$(gh api "repos/${REPO}/actions/runs/${run_id}/attempts/1/jobs" \
            --jq '.jobs[] | select(.conclusion == "failure") | .id' 2>/dev/null | head -1 || true)

        [[ -z "${job_id}" ]] && continue

        # Fetch logs and extract failure patterns
        gh api "repos/${REPO}/actions/jobs/${job_id}/logs" 2>/dev/null | \
            grep -E '(✘|TimeoutException|waitForAppReady|Error:|ERRORS!|FAILURES!)' >> "${temp_file}" || true
    done <<< "${run_ids}"

    if [[ -s "${temp_file}" ]]; then
        echo "Test failures:"
        grep -oE '✘ [^│]+' "${temp_file}" | sort | uniq -c | sort -rn | head -10 || true

        echo
        echo "Timeout patterns:"
        grep -oE 'waitForAppReady\(\) timed out[^}]+}' "${temp_file}" | head -5 || true

        echo
        echo "Page state during failures:"
        grep -oE '"koAvailable":(true|false)' "${temp_file}" | sort | uniq -c || true
        grep -oE '"mainMenuChildren":[0-9]+' "${temp_file}" | sort | uniq -c || true
    else
        echo "No failure patterns extracted"
    fi

    rm -f "${temp_file}"
}

main "$@"

#!/usr/bin/env bash
#
# Mock `gh` for BATS tests of wait-for-run.sh.
#
# Handles:
#   * `gh run watch <run-id> --exit-status --repo <repo>`
#   * `gh run view <run-id> --repo <repo> --json status,conclusion --jq ...`
#
# Each call takes the next entry from a ';'-separated script, so a
# test can lay out a sequence (first watch dies, second succeeds).
# The last entry repeats once the list runs out.
#
# Env-var contract:
#
#   MOCK_WATCH_EXITS   exit codes for successive `gh run watch` calls.
#                      Default '0'.
#   MOCK_VIEW_STATES   successive `gh run view` outputs, each
#                      "<status> <conclusion>", or "FAIL" to exit 1
#                      with no output. Default 'completed success'.
#   MOCK_STATE_DIR     where the per-verb call counters live. Required.

set -euo pipefail

{
    printf 'gh'
    for a in "$@"; do
        printf ' %s' "${a}"
    done
    printf '\n'
} >> "${MOCK_CALL_LOG:-/dev/null}"

# Print entry number <n> (1-based) of a ';'-separated list, repeating
# the last entry past the end.
nth_entry() {
    local list="$1" n="$2"
    local -a entries
    IFS=';' read -r -a entries <<< "${list}"
    if ((n > ${#entries[@]})); then
        n=${#entries[@]}
    fi
    printf '%s' "${entries[n - 1]}"
}

# Bump and print the call counter for <verb>.
next_call() {
    local file="${MOCK_STATE_DIR:?MOCK_STATE_DIR must be set}/$1.count" count=0
    if [[ -f "${file}" ]]; then
        count=$(<"${file}")
    fi
    count=$((count + 1))
    printf '%s' "${count}" > "${file}"
    printf '%s' "${count}"
}

if [[ "${1:-}" != "run" ]]; then
    echo "gh-mock: unsupported subcommand: ${1:-}" >&2
    exit 2
fi

case "${2:-}" in
    watch)
        n=$(next_call watch)
        watch_exit=$(nth_entry "${MOCK_WATCH_EXITS:-0}" "${n}")
        exit "${watch_exit}"
        ;;
    view)
        n=$(next_call view)
        state=$(nth_entry "${MOCK_VIEW_STATES:-completed success}" "${n}")
        if [[ "${state}" == "FAIL" ]]; then
            exit 1
        fi
        printf '%s\n' "${state}"
        ;;
    *)
        echo "gh-mock: unsupported run verb: ${2:-}" >&2
        exit 2
        ;;
esac

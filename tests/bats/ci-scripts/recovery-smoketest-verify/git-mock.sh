#!/usr/bin/env bash
#
# Mock `git` for BATS tests of recovery-smoketest-verify.sh.
#
# Same as the baseline test mock -- ls-remote only.

set -euo pipefail

{
    printf 'git'
    for a in "$@"; do
        printf ' %s' "${a}"
    done
    printf '\n'
} >> "${MOCK_CALL_LOG:-/dev/null}"

subcommand="${1:-}"
shift || true

case "${subcommand}" in
    ls-remote)
        printf '%s' "${MOCK_LS_REMOTE_STDOUT:-}"
        if [[ -n "${MOCK_LS_REMOTE_STDERR:-}" ]]; then
            printf '%s' "${MOCK_LS_REMOTE_STDERR}" >&2
        fi
        exit "${MOCK_LS_REMOTE_EXIT:-0}"
        ;;
    *)
        echo "git-mock: unsupported subcommand: ${subcommand}" >&2
        exit 2
        ;;
esac

#!/usr/bin/env bash
#
# Mock `git` for BATS tests of assert-release-shipped.sh.
#
# Only ls-remote is used by the script under test. Env-var contract:
#
#   MOCK_LS_REMOTE_STDOUT     text emitted on stdout for ls-remote.
#                             Set to the `<sha>\trefs/tags/<tag>` line
#                             to simulate the tag existing. Default
#                             empty (tag missing).
#   MOCK_LS_REMOTE_EXIT       exit code. Default 0 (ls-remote itself
#                             succeeded, but returned no matching
#                             refs -- that's how real git behaves for
#                             a missing tag WITHOUT --exit-code).

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
        exit "${MOCK_LS_REMOTE_EXIT:-0}"
        ;;
    *)
        echo "git-mock: unsupported subcommand: ${subcommand}" >&2
        exit 2
        ;;
esac

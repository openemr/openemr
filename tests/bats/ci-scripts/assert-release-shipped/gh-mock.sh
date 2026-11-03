#!/usr/bin/env bash
#
# Mock `gh` for BATS tests of assert-release-shipped.sh.
#
# Only `gh release view` is used. Env-var contract:
#
#   MOCK_GH_RELEASE_VIEW_EXIT   exit code. Real gh returns 0 when the
#                               release exists, 1 when it doesn't.
#                               Default 0 (release exists).

set -euo pipefail

{
    printf 'gh'
    for a in "$@"; do
        printf ' %s' "${a}"
    done
    printf '\n'
} >> "${MOCK_CALL_LOG:-/dev/null}"

subcommand="${1:-}"
shift || true

if [[ "${subcommand}" == "release" ]]; then
    verb="${1:-}"
    shift || true
    case "${verb}" in
        view)
            exit "${MOCK_GH_RELEASE_VIEW_EXIT:-0}"
            ;;
        *)
            echo "gh-mock: unsupported release verb: ${verb}" >&2
            exit 2
            ;;
    esac
fi

echo "gh-mock: unsupported subcommand: ${subcommand}" >&2
exit 2

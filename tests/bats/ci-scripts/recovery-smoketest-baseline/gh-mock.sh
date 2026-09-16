#!/usr/bin/env bash
#
# Mock `gh` for BATS tests of recovery-smoketest-baseline.sh.
#
# The script makes two gh calls (both `gh release view`):
#   1. With --json + --jq to fetch mutation-visible fields for
#      hashing.
#   2. Bare (no --json) as an explicit exit-code check so a gh
#      failure that made call #1 emit a hash-of-empty gets caught.
#
# Env-var contract:
#
#   MOCK_GH_RELEASE_VIEW_JSON   JSON string emitted on stdout for the
#                               `--jq '@json'` case. Default is a
#                               small valid Release payload; tests
#                               that want a specific-shape hash can
#                               override.
#   MOCK_GH_RELEASE_VIEW_EXIT   exit code for `gh release view`.
#                               Default 0 (success -- Release exists).
#
# Note: since `--jq '@json'` is passed to gh, real gh would apply the
# jq expression internally. This mock skips that layer and just emits
# whatever the caller set as MOCK_GH_RELEASE_VIEW_JSON directly.
# Tests that want to exercise gh's jq layer would need a real gh, not
# a mock.

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
            # Distinguish the --jq call (emits JSON we hash) from the
            # bare call (just an exit-code probe). Both are `gh release
            # view <tag> --repo ...`; the --jq flag distinguishes.
            #
            # When --jq is present, apply the expression via real jq
            # against MOCK_GH_RELEASE_VIEW_JSON. This exercises the
            # actual projection the script asks for (e.g. asset
            # `downloadCount` stripping) instead of bypassing it.
            args=("$@")
            jq_expr=""
            for ((i=0; i<${#args[@]}; i++)); do
                if [[ "${args[i]}" == "--jq" ]] && ((i+1 < ${#args[@]})); then
                    jq_expr="${args[i+1]}"
                    break
                fi
            done
            # Compute default in a variable to avoid brace-counting
            # ambiguity in ${VAR:-DEFAULT} when the DEFAULT itself
            # contains braces (as JSON does).
            default_json='{"body":"","name":"OpenEMR 8.4.0","isDraft":false,"isPrerelease":false,"targetCommitish":"rel-840","publishedAt":"2026-09-10T00:00:00Z","createdAt":"2026-09-10T00:00:00Z","assets":[]}'
            payload="${MOCK_GH_RELEASE_VIEW_JSON:-${default_json}}"
            if [[ -n "${jq_expr}" ]]; then
                printf '%s' "${payload}" | jq "${jq_expr}"
            fi
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

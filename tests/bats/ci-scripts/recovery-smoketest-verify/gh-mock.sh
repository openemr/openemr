#!/usr/bin/env bash
#
# Mock `gh` for BATS tests of recovery-smoketest-verify.sh.
#
# Handles:
#   * `gh release view <tag> --repo <repo>` (bare probe)
#   * `gh release view <tag> --repo <repo> --json ... --jq '@json'`
#     (hash payload)
#   * `gh run view <run-id> --repo <repo> --json jobs --jq ...`
#     (publish-job-status check)
#
# Env-var contract:
#
#   MOCK_GH_RELEASE_VIEW_JSON      JSON payload for the hash call.
#                                  Default: matches the baseline
#                                  mock's default so tests get an
#                                  identical hash between baseline
#                                  + verify runs.
#   MOCK_GH_RELEASE_VIEW_EXIT      exit code for both release view
#                                  calls. Default 0.
#
#   MOCK_PUBLISH_CONCLUSION_<RID>  per-run conclusion emitted for
#                                  `gh run view <RID> ... --json
#                                  jobs --jq ...`. Default 'skipped'
#                                  (happy path).

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

case "${subcommand}" in
    release)
        verb="${1:-}"
        shift || true
        case "${verb}" in
            view)
                if printf '%s\n' "$@" | grep -q -- '--jq'; then
                    # Compute default in a variable to avoid brace-
                    # counting ambiguity in ${VAR:-DEFAULT} when the
                    # DEFAULT itself contains braces (as JSON does).
                    default_json='{"body":"","name":"OpenEMR 8.4.0","isDraft":false,"isPrerelease":false,"targetCommitish":"rel-840","publishedAt":"2026-09-10T00:00:00Z","createdAt":"2026-09-10T00:00:00Z","assets":[]}'
                    printf '%s' "${MOCK_GH_RELEASE_VIEW_JSON:-$default_json}"
                fi
                exit "${MOCK_GH_RELEASE_VIEW_EXIT:-0}"
                ;;
            *)
                echo "gh-mock: unsupported release verb: ${verb}" >&2
                exit 2
                ;;
        esac
        ;;
    run)
        verb="${1:-}"
        shift || true
        case "${verb}" in
            view)
                # `gh run view <RID> --repo ... --json jobs --jq ...`
                # First positional after `view` is the run ID.
                rid="${1:-}"
                # Look up per-RID conclusion; default 'skipped'.
                var_name="MOCK_PUBLISH_CONCLUSION_${rid}"
                # ${!var} does indirect variable expansion.
                conclusion="${!var_name:-skipped}"
                printf '%s\n' "${conclusion}"
                exit 0
                ;;
            *)
                echo "gh-mock: unsupported run verb: ${verb}" >&2
                exit 2
                ;;
        esac
        ;;
    *)
        echo "gh-mock: unsupported subcommand: ${subcommand}" >&2
        exit 2
        ;;
esac

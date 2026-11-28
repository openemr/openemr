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
#   MOCK_PUBLISH_CONCLUSION_<RID>  per-run publish-job conclusion
#                                  emitted for `gh run view <RID>
#                                  ... --json status,jobs --jq ...`.
#                                  Default 'skipped' (happy path).
#                                  Set to '' for "no publish job".
#   MOCK_RUN_STATUS_<RID>          per-run status emitted before the
#                                  conclusion. Default 'completed'.

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
                # When --jq is present, apply the expression via real
                # jq against MOCK_GH_RELEASE_VIEW_JSON so the actual
                # projection the script asks for is exercised (e.g.
                # asset `downloadCount` stripping).
                args=("$@")
                jq_expr=""
                for ((i=0; i<${#args[@]}; i++)); do
                    if [[ "${args[i]}" == "--jq" ]] && ((i+1 < ${#args[@]})); then
                        jq_expr="${args[i+1]}"
                        break
                    fi
                done
                # Compute default in a variable to avoid brace-
                # counting ambiguity in ${VAR:-DEFAULT} when the
                # DEFAULT itself contains braces (as JSON does).
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
        ;;
    run)
        verb="${1:-}"
        shift || true
        case "${verb}" in
            view)
                # `gh run view <RID> --repo ... --json jobs --jq ...`
                # First positional after `view` is the run ID.
                rid="${1:-}"
                # MOCK_GH_RUN_VIEW_EXIT_<RID>: per-run exit code
                # override (default 0). Lets tests exercise the
                # gh-run-view-failed branch in verify.sh.
                exit_var="MOCK_GH_RUN_VIEW_EXIT_${rid}"
                run_exit="${!exit_var:-0}"
                if [[ "${run_exit}" != "0" ]]; then
                    exit "${run_exit}"
                fi
                # Look up per-RID status + conclusion, printed as
                # "<status> <conclusion>" like the script's --jq.
                # ${!var} does indirect variable expansion; `-` (not
                # `:-`) keeps an explicitly empty conclusion empty.
                status_var="MOCK_RUN_STATUS_${rid}"
                run_status="${!status_var:-completed}"
                var_name="MOCK_PUBLISH_CONCLUSION_${rid}"
                conclusion="${!var_name-skipped}"
                printf '%s %s\n' "${run_status}" "${conclusion}"
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

# Helpers for BATS tests of .github/scripts/validate-source-run.sh.
#
# Pure JSON-parse script (no docker, no network, no gh) -- tests build
# RUN_JSON blobs as here-strings and pass them via env. No mock needed.
#
# Pattern follows tests/bats/ci-scripts/expand-docker-tags/helpers.bash --
# script path resolved at load time; a small make_run_json() convenience
# builds well-shaped fixture blobs.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034  # referenced from .bats files
VALIDATE_SOURCE_RUN_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/validate-source-run.sh"

setup_test_dir() {
    # `mktemp -d` (no `-t <template>`) -- BusyBox's mktemp in the
    # bats/bats:1.13.0 Alpine image rejects the `-t` form GNU mktemp
    # accepts.
    CWD=$(mktemp -d)
    cd "${CWD}" || exit 1
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
    unset RUN_JSON SOURCE_RUN_ID EXPECTED_WORKFLOW_PATH
    unset EXPECTED_CANDIDATE_TAG CANDIDATE_TAG AGE_CEILING_HOURS
}

# Build a RUN_JSON blob matching the shape gh api returns for
# /repos/{owner}/{repo}/actions/runs/{id}. Arg 1 is the path, arg 2 is
# the created_at ISO timestamp, arg 3 (optional) is the run_attempt
# (default "1"). Additional fields the real API returns are omitted --
# the script only reads .path, .created_at, .run_attempt.
#
# Usage:
#   RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "2026-07-30T12:00:00Z")
#   RUN_JSON=$(make_run_json ".github/workflows/docker-build-release.yml" "2026-07-30T12:00:00Z" "2")
make_run_json() {
    local path="$1"
    local created_at="$2"
    local run_attempt="${3:-1}"
    printf '{"path":"%s","created_at":"%s","run_attempt":%s}' \
        "${path}" "${created_at}" "${run_attempt}"
}

# ISO-8601 timestamp N hours before now. Uses `date -d @<epoch>` (both
# GNU and BusyBox date accept this form) rather than `date -d "N hours
# ago"` (GNU-only; the bats/bats:1.13.0 Alpine image's BusyBox date
# rejects the relative form).
hours_ago() {
    local hours="$1"
    local now_epoch
    now_epoch=$(date -u +%s)
    date -u -d "@$(( now_epoch - hours * 3600 ))" +%Y-%m-%dT%H:%M:%SZ
}

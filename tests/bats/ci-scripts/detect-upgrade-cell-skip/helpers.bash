# Helpers for BATS tests of .github/scripts/detect-upgrade-cell-skip.sh.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
DETECT_UPGRADE_CELL_SKIP_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/detect-upgrade-cell-skip.sh"

setup_test_dir() {
    CWD=$(mktemp -d)
    cd "${CWD}" || exit 1
    # Fresh GITHUB_OUTPUT sink per test -- the script prefers writing
    # here over stdout when it's set. Tests read this file after `run`.
    GITHUB_OUTPUT="${CWD}/github-output"
    : > "${GITHUB_OUTPUT}"
    export GITHUB_OUTPUT
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
    unset FROM_REV TO_REV FROM_TAG TO_TAG RELEASE_TARGETS_PATH GITHUB_OUTPUT
}

# Write a minimal release-targets.yml fixture at ${CWD}/release-targets.yml
# and export RELEASE_TARGETS_PATH so the script reads it. The fixture
# only needs the master row's docker_tags line -- the script's awk pass
# stops at that line, so no other rows are required.
#
# Args:
#   $1  Comma-separated docker_tags value for the master row (e.g.
#       "8.5.0,dev" or "8.5.0,dev,next"). Empty string = omit the row
#       entirely (simulates master missing from release-targets.yml,
#       which would be a broken repo state).
write_release_targets() {
    local master_tags="$1"
    local path="${CWD}/release-targets.yml"
    {
        echo "# BATS fixture -- master row only, other fields intentionally omitted"
        if [[ -n "${master_tags}" ]]; then
            echo "- branch: master"
            echo "  docker_tags: ${master_tags}"
        fi
        # Add a non-master row after so the awk-stop-at-master-docker-
        # tags behavior is exercised (grep should NOT walk into this).
        echo "- branch: rel-840"
        echo "  docker_tags: 8.4.1,latest,next"
    } > "${path}"
    RELEASE_TARGETS_PATH="${path}"
    export RELEASE_TARGETS_PATH
}

# Write a fixture where the master row exists but is missing its
# docker_tags field entirely (i.e. every field except `- branch:` was
# stripped). Rabbit-caught bug: naive `{f=1} f && /^  docker_tags:/`
# awk leaks into the next row's docker_tags. This lets us pin the
# scope-closing behavior.
#
# Args:
#   $1  Comma-separated docker_tags for the subsequent rel-840 row
#       (what a broken parser would misread as master's tags).
write_release_targets_master_missing_docker_tags() {
    local rel840_tags="$1"
    local path="${CWD}/release-targets.yml"
    {
        echo "# BATS fixture -- master row present but missing docker_tags"
        echo "- branch: master"
        echo "  openemr_version_ref: refs/heads/master"
        echo "- branch: rel-840"
        echo "  docker_tags: ${rel840_tags}"
    } > "${path}"
    RELEASE_TARGETS_PATH="${path}"
    export RELEASE_TARGETS_PATH
}

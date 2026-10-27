# Helpers for BATS tests of .github/scripts/lib/release-targets-queries.sh.
#
# The lib is SOURCED by callers, not executed. Each test sources it in a
# subshell (via `run bash -c`) so the function definitions don't leak
# into the BATS process state.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
RELEASE_TARGETS_QUERIES_LIB="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/lib/release-targets-queries.sh"

setup_test_dir() {
    CWD=$(mktemp -d)
    cd "${CWD}" || exit 1
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
}

# Run `master_row_carries_next <path>` in a subshell with the lib
# sourced. Captures the function's exit code into BATS' $status.
#
# Args:
#   $1  Path to pass as the first argument to master_row_carries_next
#       (use "" to test the "empty arg" path, or omit entirely to test
#       the "no arg" path via run_predicate_no_args).
run_predicate() {
    local path="$1"
    run bash -c "source '${RELEASE_TARGETS_QUERIES_LIB}' && master_row_carries_next '${path}'"
}

run_predicate_no_args() {
    run bash -c "source '${RELEASE_TARGETS_QUERIES_LIB}' && master_row_carries_next"
}

# Build a release-targets.yml at ${CWD}/release-targets.yml with a
# master row whose docker_tags is set to $1, optionally followed by
# non-master rows (passed as additional comma-separated tag lines).
#
# Args:
#   $1   docker_tags value for master row (empty string = omit the
#        docker_tags line within the master row).
#   $2+  Each additional arg becomes a `- branch: rel-XXX / docker_tags:
#        <arg>` row appended after master, letting us pin the awk's
#        scope-close behavior.
write_release_targets() {
    local master_tags="$1"
    shift
    local path="${CWD}/release-targets.yml"
    {
        echo "# BATS fixture"
        echo "- branch: master"
        if [[ -n "${master_tags}" ]]; then
            echo "  docker_tags: ${master_tags}"
        else
            echo "  openemr_version_ref: refs/heads/master"
        fi
        local idx=0
        for tags in "$@"; do
            idx=$((idx + 1))
            echo "- branch: rel-${idx}"
            echo "  docker_tags: ${tags}"
        done
    } > "${path}"
    echo "${path}"
}

# Build a release-targets.yml with NO master row at all (broken state;
# the predicate must return 1, not 0, under this shape).
write_release_targets_no_master() {
    local path="${CWD}/release-targets.yml"
    {
        echo "# BATS fixture -- master row intentionally absent"
        echo "- branch: rel-840"
        echo "  docker_tags: 8.4.1,latest,next"
    } > "${path}"
    echo "${path}"
}

# Write a fixture where the `- branch: master` line carries an inline
# YAML comment. yq's structural parse treats this identically to the
# bare form, so the predicate must agree -- a regex that only matches
# `/^- branch: master$/` silently flips a FALSE NEGATIVE here (fails
# to scope the master row -> emits empty tag line -> returns 1 when
# master really does carry `next`). Caller passes the master_tags to
# use.
write_release_targets_master_with_inline_comment() {
    local master_tags="$1"
    local path="${CWD}/release-targets.yml"
    {
        echo "# BATS fixture -- master row has inline comment on - branch: line"
        echo "- branch: master  # inline comment that must not break scoping"
        echo "  docker_tags: ${master_tags}"
    } > "${path}"
    echo "${path}"
}

# Write a fixture where the master row's `docker_tags:` line carries
# an inline YAML comment whose text contains tokens that could
# false-match the predicate ("next", "8.5.0,next", etc). This is the
# CRITICAL false-positive protection: a naive regex over the raw line
# treats the comment as a tag and silently stands down the release-
# mode guardrail. The predicate MUST strip the comment before the
# next-tag match.
write_release_targets_master_with_tags_comment() {
    local master_tags="$1"
    local comment_text="$2"
    local path="${CWD}/release-targets.yml"
    {
        echo "# BATS fixture -- master's docker_tags has inline comment"
        echo "- branch: master"
        echo "  docker_tags: ${master_tags}  # ${comment_text}"
    } > "${path}"
    echo "${path}"
}

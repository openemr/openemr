# Helpers for BATS tests of .github/scripts/assert-inputs-nonempty.sh.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
ASSERT_INPUTS_NONEMPTY_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/assert-inputs-nonempty.sh"

setup_test_dir() {
    CWD=$(mktemp -d)
    cd "${CWD}" || exit 1
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
    # Unset any test-only env vars that tests may have set. Iterate a
    # known set rather than "everything that isn't a bats internal";
    # tests are the ones setting these and are the ones that need to
    # clean up.
    unset FOO BAR BAZ EMPTY_ONE EMPTY_TWO VERSION RELEASE_TAG VERSION_BRANCH ARTIFACT_NAME
}

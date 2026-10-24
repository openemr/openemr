# Helpers for BATS tests of .github/scripts/validate-flag-with-reason.sh.
#
# Pure env-var validation script (no network, no filesystem beyond bash
# itself). Tests pass FLAG_NAME/FLAG_VALUE/REASON_NAME/REASON_VALUE via
# env and observe exit code + emitted ::error:: annotations.
#
# Pattern follows tests/bats/ci-scripts/validate-source-run/helpers.bash --
# script path resolved at load time.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034  # referenced from .bats files
VALIDATE_FLAG_WITH_REASON_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/validate-flag-with-reason.sh"

setup_test_dir() {
    CWD=$(mktemp -d)
    cd "${CWD}" || exit 1
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
    unset FLAG_NAME FLAG_VALUE REASON_NAME REASON_VALUE
}

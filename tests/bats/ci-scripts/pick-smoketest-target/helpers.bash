# Helpers for BATS tests of .github/scripts/pick-smoketest-target.sh.
#
# Pure file-parse script (no network, no filesystem beyond the release-
# targets YAML given via RELEASE_TARGETS_FILE). Tests generate synthetic
# release-targets.yml fixtures and observe exit code + stdout.
#
# Pattern follows tests/bats/ci-scripts/validate-flag-with-reason/helpers.bash --
# script path resolved at load time.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034  # referenced from .bats files
PICK_SMOKETEST_TARGET_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/pick-smoketest-target.sh"

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
    unset RELEASE_TARGETS_FILE
}

# Write a synthetic release-targets.yml fixture to the test CWD and
# export RELEASE_TARGETS_FILE pointing at it. Content passed as stdin.
write_fixture() {
    cat > "${CWD}/release-targets.yml"
    export RELEASE_TARGETS_FILE="${CWD}/release-targets.yml"
}

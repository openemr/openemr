# Helpers for BATS tests of .github/scripts/extract-changelog-section.sh.
#
# Loaded by extract.bats via `load 'helpers'`. No mocks needed — the
# script under test just reads a plain file and writes to stdout.
# Fresh temp working dir per test so fixture CHANGELOG files don't
# collide across cases.
#
# Pattern follows tests/bats/ci-scripts/expand-docker-tags/helpers.bash --
# standalone bash, no bats-support / bats-assert; plain [[ ]] asserts.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034  # referenced from .bats files
EXTRACT_CHANGELOG_SECTION_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/extract-changelog-section.sh"

setup_test_dir() {
    # `mktemp -d` (no `-t <template>`) — BusyBox's mktemp in the
    # bats/bats:1.13.0 Alpine image rejects the `-t` form that GNU
    # mktemp accepts.
    CWD=$(mktemp -d)
    cd "${CWD}" || exit 1
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
    unset CHANGELOG_FILE VERSION
}

# Helpers for BATS tests of .github/scripts/build-release-body.sh.
#
# Loaded by build.bats via `load 'helpers'`. No mocks needed — the
# script reads a file / stdin, applies a size threshold, and writes
# markdown. Fresh temp working dir per test.
#
# Pattern follows tests/bats/ci-scripts/expand-docker-tags/helpers.bash --
# standalone bash, no bats-support / bats-assert; plain [[ ]] asserts.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034  # referenced from .bats files
BUILD_RELEASE_BODY_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/build-release-body.sh"

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
    unset SECTION_FILE VERSION DATE CHANGELOG_URL REL_BRANCH
}

# Generate a file of exactly N bytes, all ASCII `a` characters, with
# NO trailing newline. Used to hit the truncation-boundary tests
# precisely (head -c cuts at exactly the byte count; tr doesn't add
# anything, so the file is byte-exact).
# Usage: make_body_of_size <path> <size-in-bytes>
make_body_of_size() {
    local path="$1"
    local size="$2"
    # `printf` + `head -c` gets exact byte counts regardless of locale.
    head -c "${size}" /dev/zero | tr '\0' 'a' > "${path}"
}

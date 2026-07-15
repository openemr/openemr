# Helpers for BATS tests of .github/scripts/list-manifest-paths.sh.
#
# Follows the same pattern as tests/bats/ci-scripts/validate-byte-identical/
# and tests/bats/ci-scripts/sync-byte-identical/: script path resolved at
# load time, temp dir per test, small write_manifest helper.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034  # referenced from .bats files
LIST_MANIFEST_PATHS_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/list-manifest-paths.sh"

setup_test_dir() {
    CWD=$(mktemp -d -t list-manifest-paths-test-XXXX)
    # shellcheck disable=SC2034  # referenced from .bats files
    MANIFEST="${CWD}/manifest.yml"
    cd "${CWD}" || exit 1
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
}

# Write raw YAML manifest content.
write_manifest() {
    echo "$1" > "${MANIFEST}"
}

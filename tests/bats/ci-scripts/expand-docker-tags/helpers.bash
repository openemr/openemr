# Helpers for BATS tests of .github/scripts/expand-docker-tags.sh.
#
# Follows the same pattern as the other ci-scripts BATS suites: script
# path resolved at load time; a small run_script() convenience that
# exports the two required env vars and invokes the script under `run`.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034  # referenced from .bats files
EXPAND_DOCKER_TAGS_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/expand-docker-tags.sh"

setup_test_dir() {
    CWD=$(mktemp -d -t expand-docker-tags-test-XXXX)
    cd "${CWD}" || exit 1
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
    unset INPUT_TAGS BUILD_DATE
}

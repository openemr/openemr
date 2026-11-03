# Helpers for BATS tests of .github/scripts/parse-artifact-version.sh.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
PARSE_ARTIFACT_VERSION_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/parse-artifact-version.sh"

setup_test_dir() {
    CWD=$(mktemp -d)
    cd "${CWD}" || exit 1
    GITHUB_OUTPUT="${CWD}/github-output"
    : > "${GITHUB_OUTPUT}"
    export GITHUB_OUTPUT
}

teardown_test_dir() {
    cd /
    rm -rf "${CWD}"
    unset OCI_VERSION_LABEL FALLBACK_VERSION GITHUB_OUTPUT
}

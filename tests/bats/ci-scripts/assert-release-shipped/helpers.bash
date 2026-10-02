# Helpers for BATS tests of .github/scripts/assert-release-shipped.sh.
#
# Installs git-mock + gh-mock on PATH so the script never touches the
# real network. Pattern follows tests/bats/ci-scripts/create-release-tag/
# helpers.bash.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
ASSERT_RELEASE_SHIPPED_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/assert-release-shipped.sh"

setup_test_dir() {
    CWD=$(mktemp -d)

    # Install mocks at the front of PATH.
    local mock_dir="${CWD}/.mocks"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/git-mock.sh" "${mock_dir}/git"
    cp "${__HELPERS_DIR}/gh-mock.sh" "${mock_dir}/gh"
    chmod +x "${mock_dir}/git" "${mock_dir}/gh"
    export PATH="${mock_dir}:${PATH}"

    export MOCK_CALL_LOG="${CWD}/mock-calls.log"
    : > "${MOCK_CALL_LOG}"

    cd "${CWD}" || exit 1

    # Defaults: tag exists on origin, Release exists.
    export MOCK_LS_REMOTE_STDOUT=$'abc123\trefs/tags/v8_4_0'
    export MOCK_LS_REMOTE_EXIT="0"
    export MOCK_GH_RELEASE_VIEW_EXIT="0"

    # Standard inputs.
    export RELEASE_TAG="v8_4_0"
    export REPO="openemr/openemr"
    export GH_TOKEN="mock-token"
}

teardown_test_dir() {
    cd /
    export PATH="${PATH#"${CWD}/.mocks":}"
    rm -rf "${CWD}"
    unset MOCK_CALL_LOG MOCK_LS_REMOTE_STDOUT MOCK_LS_REMOTE_EXIT MOCK_GH_RELEASE_VIEW_EXIT
    unset RELEASE_TAG REPO GH_TOKEN
}

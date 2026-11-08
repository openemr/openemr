# Helpers for BATS tests of .github/scripts/recovery-smoketest-baseline.sh.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
RECOVERY_SMOKETEST_BASELINE_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/recovery-smoketest-baseline.sh"

setup_test_dir() {
    CWD=$(mktemp -d)

    local mock_dir="${CWD}/.mocks"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/git-mock.sh" "${mock_dir}/git"
    cp "${__HELPERS_DIR}/gh-mock.sh" "${mock_dir}/gh"
    chmod +x "${mock_dir}/git" "${mock_dir}/gh"
    export PATH="${mock_dir}:${PATH}"

    export MOCK_CALL_LOG="${CWD}/mock-calls.log"
    : > "${MOCK_CALL_LOG}"

    # GITHUB_ENV: the workflow-env file the script appends to.
    export GITHUB_ENV="${CWD}/github_env"
    : > "${GITHUB_ENV}"

    cd "${CWD}" || exit 1

    # Defaults: happy path.
    export MOCK_LS_REMOTE_STDOUT=$'abc123def456\trefs/tags/v8_4_0'
    export MOCK_LS_REMOTE_EXIT="0"
    export MOCK_LS_REMOTE_STDERR=""
    export MOCK_GH_RELEASE_VIEW_JSON='{"body":"","name":"OpenEMR 8.4.0","isDraft":false,"isPrerelease":false,"targetCommitish":"rel-840","publishedAt":"2026-09-10T00:00:00Z","createdAt":"2026-09-10T00:00:00Z","assets":[]}'
    export MOCK_GH_RELEASE_VIEW_EXIT="0"

    export RELEASE_TAG="v8_4_0"
    export REPO="openemr/openemr"
    export GH_TOKEN="mock-token"
}

teardown_test_dir() {
    cd /
    export PATH="${PATH#"${CWD}/.mocks":}"
    rm -rf "${CWD}"
    unset MOCK_CALL_LOG MOCK_LS_REMOTE_STDOUT MOCK_LS_REMOTE_EXIT MOCK_LS_REMOTE_STDERR
    unset MOCK_GH_RELEASE_VIEW_JSON MOCK_GH_RELEASE_VIEW_EXIT
    unset RELEASE_TAG REPO GH_TOKEN GITHUB_ENV
}

read_github_env() {
    cat "${GITHUB_ENV}"
}

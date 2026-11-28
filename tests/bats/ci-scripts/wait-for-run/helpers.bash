# Helpers for BATS tests of .github/scripts/wait-for-run.sh.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
WAIT_FOR_RUN_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/wait-for-run.sh"

setup_test_dir() {
    CWD=$(mktemp -d)

    local mock_dir="${CWD}/.mocks"
    mkdir -p "${mock_dir}"
    cp "${__HELPERS_DIR}/gh-mock.sh" "${mock_dir}/gh"
    chmod +x "${mock_dir}/gh"
    export PATH="${mock_dir}:${PATH}"

    export MOCK_CALL_LOG="${CWD}/mock-calls.log"
    : > "${MOCK_CALL_LOG}"
    export MOCK_STATE_DIR="${CWD}"

    cd "${CWD}" || exit 1

    export REPO="openemr/openemr"
    export GH_TOKEN="mock-token"
    # No real pauses between attempts.
    export WAIT_RETRY_SECONDS="0"
}

teardown_test_dir() {
    cd /
    export PATH="${PATH#"${CWD}/.mocks":}"
    rm -rf "${CWD}"
    unset MOCK_CALL_LOG MOCK_STATE_DIR MOCK_WATCH_EXITS MOCK_VIEW_STATES
    unset REPO GH_TOKEN WAIT_RETRY_SECONDS WAIT_MAX_ATTEMPTS
}

# Number of logged gh calls whose verb is <verb> (watch|view).
call_count() {
    grep -c "^gh run $1 " "${MOCK_CALL_LOG}" || true
}

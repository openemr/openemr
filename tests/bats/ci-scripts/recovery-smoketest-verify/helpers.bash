# Helpers for BATS tests of .github/scripts/recovery-smoketest-verify.sh.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
RECOVERY_SMOKETEST_VERIFY_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/recovery-smoketest-verify.sh"

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

    export GITHUB_STEP_SUMMARY="${CWD}/step_summary"
    : > "${GITHUB_STEP_SUMMARY}"

    cd "${CWD}" || exit 1

    # Baseline values simulate the state captured by
    # recovery-smoketest-baseline.sh at smoketest start.
    export MOCK_LS_REMOTE_STDOUT=$'abc123def456\trefs/tags/v8_4_0'
    export MOCK_LS_REMOTE_EXIT="0"
    export MOCK_LS_REMOTE_STDERR=""
    export MOCK_GH_RELEASE_VIEW_JSON='{"body":"","name":"OpenEMR 8.4.0","isDraft":false,"isPrerelease":false,"targetCommitish":"rel-840","publishedAt":"2026-09-10T00:00:00Z","createdAt":"2026-09-10T00:00:00Z","assets":[]}'
    export MOCK_GH_RELEASE_VIEW_EXIT="0"

    export RELEASE_TAG="v8_4_0"
    export REPO="openemr/openemr"
    export GH_TOKEN="mock-token"
    export BASELINE_TAG_SHA="abc123def456"
    # Hash of the projected MOCK_GH_RELEASE_VIEW_JSON -- same jq
    # expression the script passes to gh (strips per-asset
    # `downloadCount`). Kept in sync with the mock's default payload
    # AND the script's --jq expression.
    #
    # The inner $(...) strips jq's trailing newline the same way
    # `release_json=$(gh ...)` does in the script -- otherwise the
    # helper's hash includes a \n the script's hash doesn't.
    export BASELINE_RELEASE_HASH="$(compute_release_hash "${MOCK_GH_RELEASE_VIEW_JSON}")"
}

compute_release_hash() {
    local payload="$1"
    local projected
    projected="$(printf '%s' "${payload}" | jq '.assets |= map(del(.downloadCount))')"
    printf '%s' "${projected}" | sha256sum | awk '{print $1}'
}

teardown_test_dir() {
    cd /
    export PATH="${PATH#"${CWD}/.mocks":}"
    rm -rf "${CWD}"
    unset MOCK_CALL_LOG MOCK_LS_REMOTE_STDOUT MOCK_LS_REMOTE_EXIT MOCK_LS_REMOTE_STDERR
    unset MOCK_GH_RELEASE_VIEW_JSON MOCK_GH_RELEASE_VIEW_EXIT
    unset RELEASE_TAG REPO GH_TOKEN BASELINE_TAG_SHA BASELINE_RELEASE_HASH
    unset GITHUB_STEP_SUMMARY
    # Unset any MOCK_PUBLISH_CONCLUSION_* / MOCK_GH_RUN_VIEW_EXIT_*
    # vars tests may have set.
    while IFS= read -r var; do unset "${var}"; done < <(compgen -e | grep -E '^MOCK_(PUBLISH_CONCLUSION|GH_RUN_VIEW_EXIT)_' || true)
}

read_step_summary() {
    cat "${GITHUB_STEP_SUMMARY}"
}

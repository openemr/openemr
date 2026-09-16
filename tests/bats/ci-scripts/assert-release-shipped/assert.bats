# BATS tests for .github/scripts/assert-release-shipped.sh.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "happy path: tag + Release both exist -> exit 0" {
    run bash "${ASSERT_RELEASE_SHIPPED_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Safety gates confirmed"* ]]
}

@test "RELEASE_TAG unset -> exit 1 with clear error" {
    unset RELEASE_TAG
    run bash "${ASSERT_RELEASE_SHIPPED_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"RELEASE_TAG env var required"* ]]
}

@test "REPO unset -> exit 1 with clear error" {
    unset REPO
    run bash "${ASSERT_RELEASE_SHIPPED_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"REPO env var required"* ]]
}

@test "tag missing on origin -> exit 2 with SAFETY GATE FAILED" {
    export MOCK_LS_REMOTE_STDOUT=""
    run bash "${ASSERT_RELEASE_SHIPPED_SCRIPT}"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"SAFETY GATE FAILED"* ]]
    [[ "${output}" == *"tag v8_4_0 does not exist on origin"* ]]
}

@test "Release missing (tag exists) -> exit 3 with SAFETY GATE FAILED" {
    export MOCK_GH_RELEASE_VIEW_EXIT="1"
    run bash "${ASSERT_RELEASE_SHIPPED_SCRIPT}"
    [[ ${status} -eq 3 ]]
    [[ "${output}" == *"SAFETY GATE FAILED"* ]]
    [[ "${output}" == *"GitHub Release v8_4_0 does not exist"* ]]
}

@test "tag substring match prevented (v8_4_0 vs v8_4_0_1)" {
    # If ls-remote returned refs/tags/v8_4_0_1 (a longer name that
    # starts with v8_4_0), the grep filter must NOT accept it. Our
    # filter is `grep -q "refs/tags/${RELEASE_TAG}$"` -- anchored
    # at end-of-line, so v8_4_0_1 doesn't match a query for v8_4_0.
    export MOCK_LS_REMOTE_STDOUT=$'abc123\trefs/tags/v8_4_0_1'
    run bash "${ASSERT_RELEASE_SHIPPED_SCRIPT}"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"tag v8_4_0 does not exist on origin"* ]]
}

@test "different tag (e.g. v8_3_0) -> queries origin correctly + happy path" {
    export RELEASE_TAG="v8_3_0"
    export MOCK_LS_REMOTE_STDOUT=$'def456\trefs/tags/v8_3_0'
    run bash "${ASSERT_RELEASE_SHIPPED_SCRIPT}"
    [[ ${status} -eq 0 ]]
    # Verify the correct tag was queried.
    grep -q "git ls-remote --tags origin refs/tags/v8_3_0" "${MOCK_CALL_LOG}"
    grep -q "gh release view v8_3_0" "${MOCK_CALL_LOG}"
}

@test "call sequence: tag-check happens BEFORE release-check (fail-fast on tag)" {
    # If tag is missing, gh release view should NOT be invoked
    # (fail-fast optimization + avoids spurious 404 in gh logs).
    export MOCK_LS_REMOTE_STDOUT=""
    run bash "${ASSERT_RELEASE_SHIPPED_SCRIPT}"
    [[ ${status} -eq 2 ]]
    # Assert git was called.
    grep -q "^git ls-remote" "${MOCK_CALL_LOG}"
    # Assert gh was NOT called (short-circuit on tag failure).
    if grep -q "^gh " "${MOCK_CALL_LOG}"; then
        echo "gh was called despite tag-check failure -- should have short-circuited" >&2
        cat "${MOCK_CALL_LOG}" >&2
        return 1
    fi
}

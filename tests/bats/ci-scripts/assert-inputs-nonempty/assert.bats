# BATS tests for .github/scripts/assert-inputs-nonempty.sh.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "happy path: all named env vars non-empty -> exit 0 with Preflight OK" {
    export FOO="hello"
    export BAR="world"
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" "test-context" FOO BAR
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Preflight OK"* ]]
    [[ "${output}" == *"test-context"* ]]
    # Summary lists names + lengths, never values (defensive against
    # tokens/secrets that might land here).
    [[ "${output}" == *"FOO (len=5)"* ]]
    [[ "${output}" == *"BAR (len=5)"* ]]
    # Values never appear in output.
    [[ "${output}" != *"hello"* ]]
    [[ "${output}" != *"world"* ]]
}

@test "one env var empty -> exit 2 with context + name in error" {
    export FOO="hello"
    export BAR=""
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" "reusable-publish-release.yml" FOO BAR
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"reusable-publish-release.yml"* ]]
    [[ "${output}" == *"required input(s) missing or empty: BAR"* ]]
    # FOO not listed as missing.
    [[ "${output}" != *"missing or empty: FOO"* ]]
}

@test "all env vars empty -> exit 2, aggregates ALL names (not just first)" {
    export EMPTY_ONE=""
    export EMPTY_TWO=""
    export BAZ=""
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" "ctx" EMPTY_ONE EMPTY_TWO BAZ
    [[ ${status} -eq 2 ]]
    # All three surface in one error, in order.
    [[ "${output}" == *"missing or empty: EMPTY_ONE EMPTY_TWO BAZ"* ]]
}

@test "env var completely unset (not just empty) -> exit 2" {
    # Nounset (set -u) inside the script must NOT abort on unset var
    # lookup; the `${!name:-}` pattern handles that.
    unset FOO
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" "ctx" FOO
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"missing or empty: FOO"* ]]
}

@test "no context label -> exit 1 with usage error" {
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"context-label was empty"* ]]
}

@test "empty-string context label -> exit 1 with usage error" {
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" ""
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"context-label was empty"* ]]
}

@test "context but no input names -> exit 1 (caller bug: nothing to check)" {
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" "ctx"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"ctx"* ]]
    [[ "${output}" == *"no input names supplied"* ]]
}

@test "mixed present + missing -> exit 2, missing list is exact" {
    export FOO="a"
    export BAR=""
    export BAZ="c"
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" "ctx" FOO BAR BAZ
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"missing or empty: BAR"* ]]
    [[ "${output}" != *"missing or empty: FOO"* ]]
    [[ "${output}" != *"missing or empty: BAZ"* ]]
}

@test "value containing spaces + special chars: length reported correctly, value NOT echoed" {
    export FOO="hello world with spaces"
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" "ctx" FOO
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"FOO (len=23)"* ]]
    [[ "${output}" != *"hello world"* ]]
}

@test "realistic reusable-publish-release.yml shape: 4 inputs, all present" {
    export VERSION="8.4.0"
    export RELEASE_TAG="v8_4_0"
    export VERSION_BRANCH="rel-840"
    export ARTIFACT_NAME="openemr-release-candidate-8.4.0"
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" \
        "reusable-publish-release.yml" \
        VERSION RELEASE_TAG VERSION_BRANCH ARTIFACT_NAME
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Preflight OK (reusable-publish-release.yml)"* ]]
}

@test "realistic reusable-publish-release.yml shape: artifact_name empty (real risk case)" {
    # Simulates a caller that fetches artifact_name from an upstream
    # job's output which happens to be empty (upstream skipped or
    # errored silently).
    export VERSION="8.4.0"
    export RELEASE_TAG="v8_4_0"
    export VERSION_BRANCH="rel-840"
    export ARTIFACT_NAME=""
    run bash "${ASSERT_INPUTS_NONEMPTY_SCRIPT}" \
        "reusable-publish-release.yml" \
        VERSION RELEASE_TAG VERSION_BRANCH ARTIFACT_NAME
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"missing or empty: ARTIFACT_NAME"* ]]
}

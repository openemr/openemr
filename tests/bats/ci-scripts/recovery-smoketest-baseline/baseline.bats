# BATS tests for .github/scripts/recovery-smoketest-baseline.sh.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "happy path: emits BASELINE_TAG_SHA + BASELINE_RELEASE_HASH to GITHUB_ENV" {
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Baseline captured"* ]]
    local emitted
    emitted="$(read_github_env)"
    [[ "${emitted}" == *"BASELINE_TAG_SHA=abc123def456"* ]]
    # Release hash is deterministic (sha256 of the mock JSON). We
    # don't assert the exact value -- that's mock-content-coupled --
    # but we DO assert it's 64 hex chars.
    [[ "${emitted}" =~ BASELINE_RELEASE_HASH=[0-9a-f]{64} ]]
}

@test "RELEASE_TAG unset -> exit 1" {
    unset RELEASE_TAG
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"RELEASE_TAG env var required"* ]]
}

@test "REPO unset -> exit 1" {
    unset REPO
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"REPO env var required"* ]]
}

@test "GITHUB_ENV unset -> exit 1" {
    unset GITHUB_ENV
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"GITHUB_ENV env var required"* ]]
}

@test "git ls-remote returns no matching tag -> exit 2" {
    export MOCK_LS_REMOTE_STDOUT=""
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"no tag SHA"* ]]
}

@test "gh release view fails -> exit 3 with actionable error" {
    export MOCK_GH_RELEASE_VIEW_EXIT="1"
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 3 ]]
    [[ "${output}" == *"gh release view failed"* ]]
    [[ "${output}" == *"Run assert-release-shipped.sh first"* ]]
}

@test "baseline hash is deterministic across identical inputs" {
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local first_hash
    first_hash=$(grep 'BASELINE_RELEASE_HASH=' "${GITHUB_ENV}" | cut -d= -f2)

    # Reset GITHUB_ENV + rerun.
    : > "${GITHUB_ENV}"
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local second_hash
    second_hash=$(grep 'BASELINE_RELEASE_HASH=' "${GITHUB_ENV}" | cut -d= -f2)

    [[ "${first_hash}" == "${second_hash}" ]]
}

@test "baseline hash differs when Release payload differs" {
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local first_hash
    first_hash=$(grep 'BASELINE_RELEASE_HASH=' "${GITHUB_ENV}" | cut -d= -f2)

    # Change the Release payload (simulates gh release edit --title).
    export MOCK_GH_RELEASE_VIEW_JSON='{"body":"","name":"OpenEMR 8.4.0 (edited)","isDraft":false,"isPrerelease":false,"targetCommitish":"rel-840","publishedAt":"2026-09-10T00:00:00Z","createdAt":"2026-09-10T00:00:00Z","assets":[]}'
    : > "${GITHUB_ENV}"
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local second_hash
    second_hash=$(grep 'BASELINE_RELEASE_HASH=' "${GITHUB_ENV}" | cut -d= -f2)

    [[ "${first_hash}" != "${second_hash}" ]]
}

@test "tag SHA captured is exactly what ls-remote returned (first whitespace-delimited field)" {
    export MOCK_LS_REMOTE_STDOUT=$'deadbeef1234\trefs/tags/v8_4_0'
    run bash "${RECOVERY_SMOKETEST_BASELINE_SCRIPT}"
    [[ ${status} -eq 0 ]]
    grep -q "BASELINE_TAG_SHA=deadbeef1234" "${GITHUB_ENV}"
}

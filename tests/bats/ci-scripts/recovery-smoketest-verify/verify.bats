# BATS tests for .github/scripts/recovery-smoketest-verify.sh.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "happy path: no run IDs -> exit 0 with unchanged state" {
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Guardrail OK"* ]]
    # Step summary populated.
    [[ "$(read_step_summary)" == *"Result: **PASSED**"* ]]
}

@test "happy path: two run IDs, both publish=skipped -> exit 0" {
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}" "variant-A:12345" "variant-B:67890"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"variant-A publish job: skipped (gate held)"* ]]
    [[ "${output}" == *"variant-B publish job: skipped (gate held)"* ]]
}

@test "tag SHA changed -> exit 2 with GUARDRAIL FAILED" {
    export MOCK_LS_REMOTE_STDOUT=$'differentSha\trefs/tags/v8_4_0'
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"GUARDRAIL FAILED"* ]]
    [[ "${output}" == *"tag v8_4_0 SHA changed"* ]]
    [[ "${output}" == *"baseline: abc123def456"* ]]
    [[ "${output}" == *"current: differentSha"* ]]
}

@test "Release hash changed -> exit 2 with GUARDRAIL FAILED" {
    export MOCK_GH_RELEASE_VIEW_JSON='{"body":"edited","name":"OpenEMR 8.4.0","isDraft":false,"isPrerelease":false,"targetCommitish":"rel-840","publishedAt":"2026-09-10T00:00:00Z","createdAt":"2026-09-10T00:00:00Z","assets":[]}'
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"GUARDRAIL FAILED"* ]]
    [[ "${output}" == *"Release v8_4_0 state hash changed"* ]]
}

@test "publish job = success (gate silently regressed) -> exit 2" {
    export MOCK_PUBLISH_CONCLUSION_12345="success"
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}" "variant-A:12345"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"GUARDRAIL FAILED (variant-A)"* ]]
    [[ "${output}" == *"publish job conclusion = 'success'"* ]]
    [[ "${output}" == *"no_publish gate silently failed on run 12345"* ]]
}

@test "publish job = failure -> exit 2 (gate ran but publish itself failed)" {
    export MOCK_PUBLISH_CONCLUSION_12345="failure"
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}" "variant-A:12345"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"publish job conclusion = 'failure'"* ]]
}

@test "empty run_id in the pair -> silently skipped (earlier step already errored)" {
    # "variant-A:" with empty run_id -- treated as "not dispatched",
    # skip publish-status check for this variant.
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}" "variant-A:" "variant-B:67890"
    [[ ${status} -eq 0 ]]
    # variant-B checked, variant-A silently skipped.
    [[ "${output}" == *"variant-B publish job: skipped"* ]]
    [[ "${output}" != *"variant-A publish job"* ]]
}

@test "multiple failures aggregate -> single exit 2 after ALL checks" {
    # Both tag SHA drift AND publish job failure -- verify BOTH
    # errors are surfaced (don't short-circuit on first failure).
    export MOCK_LS_REMOTE_STDOUT=$'wrongSha\trefs/tags/v8_4_0'
    export MOCK_PUBLISH_CONCLUSION_12345="success"
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}" "variant-A:12345"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"tag v8_4_0 SHA changed"* ]]
    [[ "${output}" == *"publish job conclusion = 'success'"* ]]
}

@test "BASELINE_TAG_SHA unset -> exit 0 with 'nothing to verify' (baseline never captured)" {
    unset BASELINE_TAG_SHA
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Baseline not captured"* ]]
    [[ "${output}" == *"nothing to verify"* ]]
}

@test "RELEASE_TAG unset -> exit 1" {
    unset RELEASE_TAG
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"RELEASE_TAG env var required"* ]]
}

@test "REPO unset -> exit 1" {
    unset REPO
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"REPO env var required"* ]]
}

@test "step summary lists all dispatched runs" {
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}" "variant-A:12345" "variant-B:67890"
    [[ ${status} -eq 0 ]]
    [[ "$(read_step_summary)" == *"Publish jobs = skipped on: variant-A:12345 variant-B:67890"* ]]
}

@test "git ls-remote nonzero exit -> exit 2 via FAILED sentinel (not early set -e)" {
    # Before the pipefail-guard fix, this would abort during
    # assignment and never reach the aggregate exit.
    export MOCK_LS_REMOTE_EXIT="128"
    export MOCK_LS_REMOTE_STDERR="fatal: unable to access remote"
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}" "variant-A:12345"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"git ls-remote failed"* ]]
    # variant-A publish check still ran despite earlier failure --
    # confirms aggregation past first failure.
    [[ "${output}" == *"variant-A publish job: skipped"* ]]
}

@test "gh release view nonzero exit -> exit 2 via FAILED sentinel" {
    export MOCK_GH_RELEASE_VIEW_EXIT="1"
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"Release v8_4_0 state hash changed"* ]]
    [[ "${output}" == *"release-missing-or-view-failed"* ]]
}

@test "gh run view nonzero exit -> exit 2 via FAILED sentinel (per-run guard)" {
    export MOCK_GH_RUN_VIEW_EXIT_12345="1"
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}" "variant-A:12345" "variant-B:67890"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"gh run view failed for run 12345"* ]]
    # variant-B not affected by variant-A's failure.
    [[ "${output}" == *"variant-B publish job: skipped"* ]]
}

@test "downloadCount-only change does NOT trigger release-hash guardrail" {
    # This is the mirror of the baseline-side downloadCount test:
    # if the smoketest downloads the tarball (which it does during
    # install-check) and downloadCount bumps, the verify step must
    # NOT flag it as a mutation. The projection strips downloadCount
    # from both sides.
    export MOCK_GH_RELEASE_VIEW_JSON='{"body":"","name":"OpenEMR 8.4.0","isDraft":false,"isPrerelease":false,"targetCommitish":"rel-840","publishedAt":"2026-09-10T00:00:00Z","createdAt":"2026-09-10T00:00:00Z","assets":[{"name":"openemr-8.4.0.tar.gz","size":123,"downloadCount":0,"digest":"sha256:aaaa"}]}'
    # Re-derive BASELINE_RELEASE_HASH against this new payload
    # (the helper computed it against the empty-assets default).
    BASELINE_RELEASE_HASH="$(compute_release_hash "${MOCK_GH_RELEASE_VIEW_JSON}")"
    export BASELINE_RELEASE_HASH

    # Now current state has bumped downloadCount but is otherwise
    # identical. Verify should pass.
    export MOCK_GH_RELEASE_VIEW_JSON='{"body":"","name":"OpenEMR 8.4.0","isDraft":false,"isPrerelease":false,"targetCommitish":"rel-840","publishedAt":"2026-09-10T00:00:00Z","createdAt":"2026-09-10T00:00:00Z","assets":[{"name":"openemr-8.4.0.tar.gz","size":123,"downloadCount":42,"digest":"sha256:aaaa"}]}'
    run bash "${RECOVERY_SMOKETEST_VERIFY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Guardrail OK"* ]]
}

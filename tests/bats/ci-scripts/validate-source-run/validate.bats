# BATS tests for .github/scripts/validate-source-run.sh
#
# Pure JSON-parse script (no docker, no gh, no network). Fixture RUN_JSON
# blobs are built via make_run_json() and passed through the env.
#
# Covers:
#   - tarball happy path (build-release.yml, fresh run, no candidate_tag)
#   - docker happy path (docker-build-release.yml, fresh run, matching
#     candidate_tag)
#   - wrong-workflow rejection (both directions)
#   - candidate_tag mismatch (wrong run id, wrong attempt, wrong prefix)
#   - age ceiling (fresh boundary just under 48h vs stale just over)
#   - AGE_CEILING_HOURS override (test hook works)
#   - malformed timestamp
#   - missing .path / .created_at / .run_attempt fields
#   - required env-var guards
#   - guard ordering: wrong-workflow rejected before age check runs
#   - stdin fallback when RUN_JSON env unset
#   - EXPECTED_CANDIDATE_TAG set but CANDIDATE_TAG unset -> clear error

load 'helpers'

# Install GNU coreutils + jq if missing. The script under test uses:
#   * `date -d "$iso_z_string"` -- GNU form; bats/bats:1.13.0's Alpine
#     base ships BusyBox date which rejects the ISO-8601 `T...Z` form.
#     GHA ubuntu-24.04 (where CI runs BATS) has GNU date natively.
#   * `jq -er` -- not in the base Alpine image; also present on
#     GHA ubuntu-24.04.
# Runs once per test file.
setup_file() {
    local need_install=0
    if ! date -u -d "2026-07-30T12:00:00Z" +%s >/dev/null 2>&1; then
        need_install=1
    fi
    if ! command -v jq >/dev/null 2>&1; then
        need_install=1
    fi
    if [[ "${need_install}" -eq 1 ]]; then
        if command -v apk >/dev/null 2>&1; then
            apk add --no-cache coreutils jq >/dev/null 2>&1 || true
        elif command -v apt-get >/dev/null 2>&1; then
            apt-get update >/dev/null 2>&1 && apt-get install -y coreutils jq >/dev/null 2>&1 || true
        fi
    fi
    if ! date -u -d "2026-07-30T12:00:00Z" +%s >/dev/null 2>&1; then
        echo "# skip-reason: GNU date not installable in this environment" >&3
        skip "GNU date (coreutils) required to parse ISO-8601 timestamps like the script does"
    fi
    if ! command -v jq >/dev/null 2>&1; then
        echo "# skip-reason: jq not installable in this environment" >&3
        skip "jq required for the script's JSON parsing"
    fi
}

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# --- happy paths ---

@test "tarball happy path: build-release.yml, fresh run, no candidate_tag" {
    RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "$(hours_ago 1)") \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"source run eligible"* ]]
    [[ "${output}" != *"::error::"* ]]
}

@test "docker happy path: docker-build-release.yml + matching candidate_tag" {
    RUN_JSON=$(make_run_json ".github/workflows/docker-build-release.yml" "$(hours_ago 2)" 1) \
    SOURCE_RUN_ID="30443247578" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/docker-build-release.yml" \
    EXPECTED_CANDIDATE_TAG="release-candidate-30443247578-1" \
    CANDIDATE_TAG="release-candidate-30443247578-1" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"source run eligible"* ]]
    [[ "${output}" == *"candidate_tag matches"* ]]
    [[ "${output}" != *"::error::"* ]]
}

@test "docker happy path: run_attempt=2 (re-run) with matching candidate_tag" {
    RUN_JSON=$(make_run_json ".github/workflows/docker-build-release.yml" "$(hours_ago 3)" 2) \
    SOURCE_RUN_ID="30443247578" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/docker-build-release.yml" \
    EXPECTED_CANDIDATE_TAG="release-candidate-30443247578-2" \
    CANDIDATE_TAG="release-candidate-30443247578-2" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

# --- wrong-workflow rejection ---

@test "wrong workflow_path -> exit 1 with clear error" {
    RUN_JSON=$(make_run_json ".github/workflows/ci.yml" "$(hours_ago 1)") \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::source run 12345 was produced by '.github/workflows/ci.yml', not '.github/workflows/build-release.yml'"* ]]
    [[ "${output}" == *"::error::this recovery workflow only accepts source runs from build-release.yml"* ]]
}

@test "docker workflow_path swap (tarball workflow passed for docker recovery) -> exit 1" {
    RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "$(hours_ago 1)") \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/docker-build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"*"docker-build-release.yml"* ]]
}

# --- candidate_tag coordinates binding ---

@test "candidate_tag mismatch (wrong run id embedded) -> exit 1" {
    RUN_JSON=$(make_run_json ".github/workflows/docker-build-release.yml" "$(hours_ago 1)" 1) \
    SOURCE_RUN_ID="30443247578" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/docker-build-release.yml" \
    EXPECTED_CANDIDATE_TAG="release-candidate-30443247578-1" \
    CANDIDATE_TAG="release-candidate-99999999999-1" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::candidate_tag mismatch"* ]]
    [[ "${output}" == *"operator supplied 'release-candidate-99999999999-1'"* ]]
    [[ "${output}" == *"would have produced 'release-candidate-30443247578-1'"* ]]
    [[ "${output}" == *"maliciously"* ]]
}

@test "candidate_tag mismatch (wrong run_attempt) -> exit 1" {
    RUN_JSON=$(make_run_json ".github/workflows/docker-build-release.yml" "$(hours_ago 1)" 3) \
    SOURCE_RUN_ID="30443247578" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/docker-build-release.yml" \
    EXPECTED_CANDIDATE_TAG="release-candidate-30443247578-3" \
    CANDIDATE_TAG="release-candidate-30443247578-1" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::candidate_tag mismatch"* ]]
    [[ "${output}" == *"attempt 3"* ]]
}

@test "caller-drift: CANDIDATE_TAG matches derived but EXPECTED_CANDIDATE_TAG is stale -> exit 1" {
    # Guards script lines around the second candidate_tag equality
    # check: script derives release-candidate-<id>-<attempt> internally
    # and asserts that (a) operator-supplied CANDIDATE_TAG matches AND
    # (b) caller-supplied EXPECTED_CANDIDATE_TAG matches. Every other
    # test keeps the two in sync; this one deliberately makes the caller
    # send a stale EXPECTED value while the operator's CANDIDATE_TAG
    # is correct, to lock in the caller-drift error path.
    RUN_JSON=$(make_run_json ".github/workflows/docker-build-release.yml" "$(hours_ago 1)" 1) \
    SOURCE_RUN_ID="30443247578" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/docker-build-release.yml" \
    CANDIDATE_TAG="release-candidate-30443247578-1" \
    EXPECTED_CANDIDATE_TAG="release-candidate-99999999999-1" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"EXPECTED_CANDIDATE_TAG"* ]]
    [[ "${output}" == *"stale inputs"* ]]
}

@test "EXPECTED_CANDIDATE_TAG set but CANDIDATE_TAG unset -> exit 1 with clear error" {
    RUN_JSON=$(make_run_json ".github/workflows/docker-build-release.yml" "$(hours_ago 1)" 1) \
    SOURCE_RUN_ID="30443247578" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/docker-build-release.yml" \
    EXPECTED_CANDIDATE_TAG="release-candidate-30443247578-1" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"CANDIDATE_TAG"* ]]
}

# --- age ceiling ---

@test "age just under 48h ceiling -> pass" {
    RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "$(hours_ago 47)") \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

@test "age just over 48h ceiling -> exit 1 with stale-run error" {
    RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "$(hours_ago 49)") \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::source run 12345 is 49h old (> 48h ceiling)"* ]]
    [[ "${output}" == *"stale"* ]]
    [[ "${output}" == *"Recovery paths"* ]]
}

@test "AGE_CEILING_HOURS=1 override rejects a 2h-old run" {
    RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "$(hours_ago 2)") \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
    AGE_CEILING_HOURS="1" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"> 1h ceiling"* ]]
}

# --- malformed RUN_JSON ---

@test "malformed created_at timestamp -> exit 1 with parse error" {
    RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "definitely-not-a-timestamp") \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"created_at 'definitely-not-a-timestamp' is not a parseable timestamp"* ]]
}

@test "RUN_JSON missing .path -> exit 1 with malformed-blob error" {
    RUN_JSON='{"created_at":"2026-07-30T12:00:00Z","run_attempt":1}' \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"missing required field '.path'"* ]]
}

@test "RUN_JSON missing .created_at -> exit 1 with malformed-blob error" {
    RUN_JSON='{"path":".github/workflows/build-release.yml","run_attempt":1}' \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"missing required field '.created_at'"* ]]
}

@test "RUN_JSON missing .run_attempt (docker path only) -> exit 1" {
    RUN_JSON='{"path":".github/workflows/docker-build-release.yml","created_at":"'"$(hours_ago 1)"'"}' \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/docker-build-release.yml" \
    EXPECTED_CANDIDATE_TAG="release-candidate-12345-1" \
    CANDIDATE_TAG="release-candidate-12345-1" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"missing required field '.run_attempt'"* ]]
}

@test "RUN_JSON missing .run_attempt is OK on tarball path (candidate binding disabled)" {
    # Tarball recovery doesn't need .run_attempt -- only docker
    # candidate_tag binding does. Verify the script doesn't demand it
    # when EXPECTED_CANDIDATE_TAG is unset.
    RUN_JSON='{"path":".github/workflows/build-release.yml","created_at":"'"$(hours_ago 1)"'"}' \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 0 ]]
}

# --- required env-var guards ---

@test "missing SOURCE_RUN_ID -> non-zero exit" {
    unset SOURCE_RUN_ID
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
    RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "$(hours_ago 1)") \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"SOURCE_RUN_ID"* ]]
}

@test "missing EXPECTED_WORKFLOW_PATH -> non-zero exit" {
    unset EXPECTED_WORKFLOW_PATH
    SOURCE_RUN_ID="12345" \
    RUN_JSON=$(make_run_json ".github/workflows/build-release.yml" "$(hours_ago 1)") \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"EXPECTED_WORKFLOW_PATH"* ]]
}

@test "empty RUN_JSON env AND empty stdin -> non-zero exit with clear error" {
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash -c "printf '' | bash '${VALIDATE_SOURCE_RUN_SCRIPT}'"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"RUN_JSON is empty"* ]]
}

# --- guard ordering: cheapest-first ---

@test "wrong workflow reported BEFORE age check (guard ordering)" {
    # A run that is BOTH wrong-workflow AND too-old should fail with the
    # workflow error, not the age error -- workflow check is cheaper and
    # runs first per the audit's ordering directive.
    RUN_JSON=$(make_run_json ".github/workflows/wrong.yml" "$(hours_ago 100)") \
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash "${VALIDATE_SOURCE_RUN_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"was produced by"* ]]
    # Age error should NOT appear -- script exited before reaching that check.
    [[ "${output}" != *"ceiling"* ]]
}

# --- stdin form ---

@test "RUN_JSON via stdin (env unset) works" {
    local json
    json=$(make_run_json ".github/workflows/build-release.yml" "$(hours_ago 1)")
    unset RUN_JSON
    SOURCE_RUN_ID="12345" \
    EXPECTED_WORKFLOW_PATH=".github/workflows/build-release.yml" \
        run bash -c "printf '%s' '${json}' | bash '${VALIDATE_SOURCE_RUN_SCRIPT}'"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"source run eligible"* ]]
}

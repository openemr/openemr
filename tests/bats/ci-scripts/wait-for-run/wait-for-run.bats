# BATS tests for .github/scripts/wait-for-run.sh.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "watch succeeds first time -> exit 0, no status check" {
    run bash "${WAIT_FOR_RUN_SCRIPT}" 12345
    [[ ${status} -eq 0 ]]
    [[ "$(call_count watch)" -eq 1 ]]
    [[ "$(call_count view)" -eq 0 ]]
    grep -q '^gh run watch 12345 --exit-status --repo openemr/openemr$' "${MOCK_CALL_LOG}"
}

@test "watch fails, run completed with failure -> exit 1 naming the conclusion" {
    export MOCK_WATCH_EXITS="1"
    export MOCK_VIEW_STATES="completed failure"
    run bash "${WAIT_FOR_RUN_SCRIPT}" 12345
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"Run 12345 completed with conclusion 'failure'."* ]]
    [[ "$(call_count watch)" -eq 1 ]]
}

@test "watch fails, run completed with cancelled -> exit 1" {
    export MOCK_WATCH_EXITS="1"
    export MOCK_VIEW_STATES="completed cancelled"
    run bash "${WAIT_FOR_RUN_SCRIPT}" 12345
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"conclusion 'cancelled'"* ]]
}

@test "watch fails but run completed with success -> exit 0" {
    export MOCK_WATCH_EXITS="1"
    export MOCK_VIEW_STATES="completed success"
    run bash "${WAIT_FOR_RUN_SCRIPT}" 12345
    [[ ${status} -eq 0 ]]
}

@test "watch dies on an API error mid-run, then succeeds -> exit 0 after one retry" {
    # The 2026-11-25 nightly: gh run watch got an HTTP 500 from the
    # jobs endpoint while the run was still in progress.
    export MOCK_WATCH_EXITS="1;0"
    export MOCK_VIEW_STATES="in_progress "
    run bash "${WAIT_FOR_RUN_SCRIPT}" 12345
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"still 'in_progress' (attempt 1/5); watching again."* ]]
    [[ "$(call_count watch)" -eq 2 ]]
}

@test "status check itself fails, then watch succeeds -> exit 0" {
    export MOCK_WATCH_EXITS="1;0"
    export MOCK_VIEW_STATES="FAIL"
    run bash "${WAIT_FOR_RUN_SCRIPT}" 12345
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Couldn't read the status of run 12345"* ]]
    [[ "$(call_count watch)" -eq 2 ]]
}

@test "run never completes -> exit 1 after WAIT_MAX_ATTEMPTS watches" {
    export WAIT_MAX_ATTEMPTS="3"
    export MOCK_WATCH_EXITS="1"
    export MOCK_VIEW_STATES="in_progress "
    run bash "${WAIT_FOR_RUN_SCRIPT}" 12345
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"Gave up waiting for run 12345 after 3 watch attempts"* ]]
    [[ "$(call_count watch)" -eq 3 ]]
}

@test "missing run id -> exit 2" {
    run bash "${WAIT_FOR_RUN_SCRIPT}"
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"run id argument required"* ]]
}

@test "REPO unset -> exit 2" {
    unset REPO
    run bash "${WAIT_FOR_RUN_SCRIPT}" 12345
    [[ ${status} -eq 2 ]]
    [[ "${output}" == *"REPO env var required"* ]]
}

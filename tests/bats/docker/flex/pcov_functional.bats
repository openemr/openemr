# BATS: flex pcov.sh — functional tests (validation and exit codes)

load '../test_helper/bats-support/load'
load '../test_helper/bats-assert/load'
load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    PCOV="${SCRIPT_DIR}/pcov.sh"
    [[ -f "$PCOV" ]]
}

@test "flex pcov: exits 1 when PCOV_ON is not true" {
    run bash -c "PCOV_ON=false '$PCOV' 2>&1"
    assert_failure 1
    assert_output --partial "Error: PCOV script called but PCOV_ON is not enabled"
}

@test "flex pcov: exits 1 when PCOV_ON unset" {
    run bash -c "unset PCOV_ON; '$PCOV' 2>&1"
    assert_failure 1
    assert_output --partial "PCOV_ON: unbound variable"
}

@test "flex pcov: exits 1 when PCOV_ON=1 (numeric, not true)" {
    # Script expects literal "true" per comment in pcov.sh
    run bash -c "PCOV_ON=1 '$PCOV' 2>&1"
    assert_failure 1
    assert_output --partial "Error: PCOV script called but PCOV_ON is not enabled"
}

# BATS: release kcov-wrapper.sh — coverage wrapper

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "kcov-wrapper.sh: creates coverage directory" {
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'coverage'
}

@test "kcov-wrapper.sh: runs openemr.sh under kcov" {
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'kcov'
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'openemr.sh'
}

@test "kcov-wrapper.sh: includes devtoolsLibrary.source in coverage" {
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'devtoolsLibrary.source'
}

@test "kcov-wrapper.sh: starts httpd after coverage" {
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'httpd'
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'FOREGROUND'
}

@test "kcov-wrapper.sh: uses exec for httpd" {
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'exec'
}

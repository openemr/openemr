# BATS: flex pcov.sh — PCOV coverage

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "flex pcov.sh: PCOV_ON check" {
    assert_script_contains "${SCRIPT_DIR}/pcov.sh" 'PCOV_ON'
}

@test "flex pcov.sh: php-pcov-configured marker" {
    assert_script_contains "${SCRIPT_DIR}/pcov.sh" 'php-pcov-configured'
}

@test "flex pcov.sh: pecl-pcov and pcov settings" {
    assert_script_contains "${SCRIPT_DIR}/pcov.sh" 'pecl-pcov'
    assert_script_contains "${SCRIPT_DIR}/pcov.sh" 'pcov.enabled=1'
    assert_script_contains "${SCRIPT_DIR}/pcov.sh" 'pcov.directory'
}

@test "flex pcov.sh: pcov.directory openemr" {
    assert_script_contains "${SCRIPT_DIR}/pcov.sh" 'pcov.directory'
}

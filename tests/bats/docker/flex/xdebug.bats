# BATS: flex xdebug.sh

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "flex xdebug.sh: XDEBUG_ON and PHP_VERSION_ABBR" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'XDEBUG_ON'
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'PHP_VERSION_ABBR'
}

@test "flex xdebug.sh: php-xdebug-configured marker" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'php-xdebug-configured'
}

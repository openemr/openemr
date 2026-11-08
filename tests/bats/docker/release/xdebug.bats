# BATS: release xdebug.sh — XDebug configuration

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "xdebug.sh: validates XDEBUG_ON or XDEBUG_IDE_KEY" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'XDEBUG_ON'
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'XDEBUG_IDE_KEY'
}

@test "xdebug.sh: uses php-xdebug-configured marker" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'php-xdebug-configured'
}

@test "xdebug.sh: references PHP_VERSION_ABBR" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'PHP_VERSION_ABBR'
}

@test "xdebug.sh: installs pecl-xdebug" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'pecl-xdebug'
}

@test "xdebug.sh: configures zend_extension xdebug" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'zend_extension'
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'xdebug.so'
}

@test "xdebug.sh: xdebug.mode and client_port" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'xdebug.mode'
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'client_port'
}

@test "xdebug.sh: XDEBUG_CLIENT_HOST optional" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'XDEBUG_CLIENT_HOST'
}

@test "xdebug.sh: creates /tmp/xdebug.log" {
    assert_script_contains "${SCRIPT_DIR}/xdebug.sh" 'xdebug.log'
}

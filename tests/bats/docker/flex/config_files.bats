# BATS: flex config files — Dockerfile, configs/php*

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "flex Dockerfile: FROM alpine" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'FROM alpine'
}

@test "flex configs: php8.2 php.ini" {
    assert_file_exists "${SCRIPT_DIR}/configs/php8.2/php.ini"
}

@test "flex configs: php8.3 php.ini" {
    assert_file_exists "${SCRIPT_DIR}/configs/php8.3/php.ini"
}

@test "flex configs: php8.4 php.ini" {
    assert_file_exists "${SCRIPT_DIR}/configs/php8.4/php.ini"
}

@test "flex configs: php8.5 php.ini" {
    assert_file_exists "${SCRIPT_DIR}/configs/php8.5/php.ini"
}

@test "flex openemr.conf: exists" {
    assert_file_exists "${SCRIPT_DIR}/openemr.conf"
}

@test "flex auto_configure.php: exists" {
    assert_file_exists "${SCRIPT_DIR}/auto_configure.php"
}

@test "flex: no upgrade directory (flex has no upgrade scripts)" {
    # Flex containers don't have versioned upgrade scripts
    [[ ! -d "${SCRIPT_DIR}/upgrade" ]] || [[ -z "$(ls -A "${SCRIPT_DIR}/upgrade" 2>/dev/null)" ]]
}

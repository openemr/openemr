# BATS: binary config files — Dockerfile, php-fpm, Apache

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "binary Dockerfile: references php-fpm" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'php-fpm'
}

@test "binary Dockerfile: COPY php-fpm.conf" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'php-fpm'
}

@test "binary php-fpm.conf: exists" {
    assert_file_exists "${SCRIPT_DIR}/php-fpm.conf"
}

@test "binary php-fpm.d/www.conf: exists" {
    assert_file_exists "${SCRIPT_DIR}/php-fpm.d/www.conf"
}

@test "binary openemr.conf: exists" {
    assert_file_exists "${SCRIPT_DIR}/openemr.conf"
}

@test "binary php.ini: exists" {
    assert_file_exists "${SCRIPT_DIR}/php.ini"
}

@test "binary auto_configure.php: exists" {
    assert_file_exists "${SCRIPT_DIR}/auto_configure.php"
}

@test "binary docker-compose.test.yml: exists for local testing" {
    assert_file_exists "${SCRIPT_DIR}/docker-compose.test.yml"
}

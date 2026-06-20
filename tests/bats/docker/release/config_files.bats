# BATS: release config files — Dockerfile, Apache, PHP

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "Dockerfile: FROM alpine" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'FROM alpine'
}

@test "Dockerfile: PHP and Apache" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'php'
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'apache'
}

@test "Dockerfile: openemr.sh as entrypoint or CMD" {
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'openemr.sh'
}

@test "openemr.conf: LoadModule rewrite" {
    assert_file_contains "${SCRIPT_DIR}/openemr.conf" 'LoadModule'
    assert_file_contains "${SCRIPT_DIR}/openemr.conf" 'rewrite'
}

@test "openemr.conf: security or ServerTokens" {
    assert_file_contains "${SCRIPT_DIR}/openemr.conf" 'ServerTokens\|ServerSignature\|Security'
}

@test "php.ini: exists" {
    assert_file_exists "${SCRIPT_DIR}/php.ini"
}

@test "upgrade/docker-version: single positive integer" {
    assert_file_exists "${SCRIPT_DIR}/upgrade/docker-version"
    run cat "${SCRIPT_DIR}/upgrade/docker-version"
    [[ $output =~ ^[0-9]+$ ]]
    [[ $output -ge 1 ]]
}

@test "auto_configure.php: exists" {
    assert_file_exists "${SCRIPT_DIR}/auto_configure.php"
}

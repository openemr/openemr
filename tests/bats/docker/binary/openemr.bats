# BATS: binary openemr.sh — PHP-FPM + Apache startup

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "binary openemr.sh: PHP-FPM and Apache in header" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'PHP-FPM'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'Apache'
}

@test "binary openemr.sh: defines OE_ROOT, AUTO_CONFIG, SQLCONF_FILE" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'OE_ROOT='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'AUTO_CONFIG='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'SQLCONF_FILE='
}

@test "binary openemr.sh: sources devtoolsLibrary.source" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'devtoolsLibrary.source'
}

@test "binary openemr.sh: starts php-fpm and httpd" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'php-fpm'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'httpd'
}

@test "binary openemr.sh: wait_for_mysql and is_configured" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'wait_for_mysql()'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'is_configured()'
}

@test "binary openemr.sh: MANUAL_SETUP, K8S, SWARM_MODE" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'MANUAL_SETUP='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'SWARM_MODE='
}

@test "binary openemr.sh: exec httpd FOREGROUND" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'exec'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'FOREGROUND'
}

@test "binary openemr.sh: /usr/local/bin/php-fpm or php-fpm" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'php-fpm'
}

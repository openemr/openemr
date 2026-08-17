# BATS: release openemr.sh — main startup script behavior and structure

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "openemr.sh: defines path constants OE_ROOT, AUTO_CONFIG, SQLCONF_FILE" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'OE_ROOT='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'AUTO_CONFIG='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'SQLCONF_FILE='
}

@test "openemr.sh: sources devtoolsLibrary.source" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'devtoolsLibrary.source'
}

@test "openemr.sh: defines MYSQL_* and OE_* env defaults" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'MYSQL_HOST='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'MYSQL_ROOT_PASS='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'MYSQL_USER='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'MYSQL_DATABASE='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'OE_USER='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'OE_PASS='
}

@test "openemr.sh: defines MANUAL_SETUP, K8S, SWARM_MODE" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'MANUAL_SETUP='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'K8S='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'SWARM_MODE='
}

@test "openemr.sh: defines AUTHORITY and OPERATOR" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'AUTHORITY='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'OPERATOR='
}

@test "openemr.sh: implements wait_for_mysql" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'wait_for_mysql()'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'mysqladmin ping'
}

@test "openemr.sh: implements wait_for_redis" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'wait_for_redis()'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'REDIS_SERVER'
}

@test "openemr.sh: implements is_configured" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'is_configured()'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'SQLCONF_FILE'
}

@test "openemr.sh: implements run_auto_configure" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'run_auto_configure()'
}

@test "openemr.sh: uses set -euo pipefail" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'set -euo pipefail'
}

@test "openemr.sh: references auto_configure.php" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'auto_configure'
}

@test "openemr.sh: K8S admin sets OPERATOR=no" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'K8S.*admin'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'OPERATOR=no'
}

@test "openemr.sh: K8S worker sets AUTHORITY=no" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'K8S.*worker'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'AUTHORITY=no'
}

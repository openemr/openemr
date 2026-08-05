# BATS: release openemr.sh — functional tests (early execution and role logic)

load '../test_helper/bats-support/load'
load '../test_helper/bats-assert/load'
load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    OPENEMR="${SCRIPT_DIR}/openemr.sh"
    [[ -f "$OPENEMR" ]]
}

@test "openemr: with K8S=admin script does not immediately syntax-error" {
    # Quick run to ensure script parses and starts; it will fail when trying to run mysqladmin etc.
    run bash -c "if command -v timeout >/dev/null 2>&1; then timeout 5 env K8S=admin MYSQL_HOST=localhost MYSQL_ROOT_PASS=root '$OPENEMR' 2>&1; else env K8S=admin MYSQL_HOST=localhost MYSQL_ROOT_PASS=root '$OPENEMR' 2>&1; fi"
    refute_output --partial "syntax error"
    [ "$status" -ne 2 ]
}

@test "openemr: CONFIGURATION is set after prepareVariables when sourced with env" {
    # Source only the library and openemr.sh vars then call prepareVariables via library
    run bash -c "export MYSQL_HOST=db MYSQL_ROOT_PASS=secret; source '${SCRIPT_DIR}/utilities/devtoolsLibrary.source'; prepareVariables; echo \"CONFIG=\$CONFIGURATION\""
    assert_success
    assert_output --partial "server=db"
    assert_output --partial "rootpass=secret"
}

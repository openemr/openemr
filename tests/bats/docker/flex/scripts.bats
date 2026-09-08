# BATS tests for OpenEMR Docker flex bash scripts

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "flex: script directory exists" {
    [[ -d "$SCRIPT_DIR" ]]
}

@test "flex: openemr.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/openemr.sh"
}

@test "flex: openemr.sh uses bash and sources devtoolsLibrary" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'devtoolsLibrary.source'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'set -euo pipefail'
}

@test "flex: openemr.sh references FLEX_REPOSITORY" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'FLEX_REPOSITORY'
}

@test "flex: ssl.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/ssl.sh"
}

@test "flex: ssl.sh handles self-signed certificate" {
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'selfsigned'
}

@test "flex: xdebug.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/xdebug.sh"
}

@test "flex: pcov.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/pcov.sh"
}

@test "flex: kcov-wrapper.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/kcov-wrapper.sh"
}

@test "flex: utilities/unlock_admin.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/utilities/unlock_admin.sh"
}

@test "flex: utilities/unlock_admin.sh invokes unlock_admin.php" {
    assert_script_contains "${SCRIPT_DIR}/utilities/unlock_admin.sh" 'unlock_admin.php'
}

@test "flex: devtoolsLibrary.source exists" {
    assert_file_exists "${SCRIPT_DIR}/utilities/devtoolsLibrary.source"
}

@test "flex: Dockerfile exists" {
    assert_file_exists "${SCRIPT_DIR}/Dockerfile"
}

@test "flex: configs directory exists with php versions" {
    [[ -d "${SCRIPT_DIR}/configs" ]]
    # At least one PHP version config
    ls "${SCRIPT_DIR}"/configs/php*/php.ini 2>/dev/null | head -1
    [[ $? -eq 0 ]]
}

# BATS tests for OpenEMR Docker binary bash scripts

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "binary: script directory exists" {
    [[ -d "$SCRIPT_DIR" ]]
}

@test "binary: openemr.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/openemr.sh"
}

@test "binary: openemr.sh uses bash and sources devtoolsLibrary" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'devtoolsLibrary.source'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'set -euo pipefail'
}

@test "binary: openemr.sh defines OE_ROOT and AUTO_CONFIG" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'OE_ROOT='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'AUTO_CONFIG='
}

@test "binary: ssl.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/ssl.sh"
}

@test "binary: ssl.sh handles self-signed certificate" {
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'selfsigned'
}

@test "binary: utilities/unlock_admin.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/utilities/unlock_admin.sh"
}

@test "binary: utilities/unlock_admin.sh invokes unlock_admin.php" {
    assert_script_contains "${SCRIPT_DIR}/utilities/unlock_admin.sh" 'unlock_admin.php'
}

@test "binary: upgrade scripts exist and have valid syntax" {
    for i in 1 2 3 4 5 6 7 8; do
        assert_script_syntax "${SCRIPT_DIR}/upgrade/fsupgrade-${i}.sh"
    done
}

@test "binary: devtoolsLibrary.source exists" {
    assert_file_exists "${SCRIPT_DIR}/utilities/devtoolsLibrary.source"
}

@test "binary: Dockerfile exists" {
    assert_file_exists "${SCRIPT_DIR}/Dockerfile"
}

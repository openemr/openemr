# BATS tests for OpenEMR Docker release bash scripts

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "script directory exists" {
    [[ -d "$SCRIPT_DIR" ]]
}

@test "openemr.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/openemr.sh"
}

@test "openemr.sh uses bash and sources devtoolsLibrary" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'devtoolsLibrary.source'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'set -euo pipefail'
}

@test "openemr.sh defines OE_ROOT and AUTO_CONFIG" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'OE_ROOT='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'AUTO_CONFIG='
}

@test "ssl.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/ssl.sh"
}

@test "ssl.sh handles self-signed certificate" {
    assert_script_contains "${SCRIPT_DIR}/ssl.sh" 'selfsigned'
}

@test "xdebug.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/xdebug.sh"
}

@test "kcov-wrapper.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/kcov-wrapper.sh"
}

@test "utilities/unlock_admin.sh exists and has valid syntax" {
    assert_script_syntax "${SCRIPT_DIR}/utilities/unlock_admin.sh"
}

@test "utilities/unlock_admin.sh invokes unlock_admin.php" {
    assert_script_contains "${SCRIPT_DIR}/utilities/unlock_admin.sh" 'unlock_admin.php'
}

@test "upgrade scripts exist and have valid syntax" {
    # Enumerate the fsupgrade-*.sh files at test time rather than hardcoding a
    # 1..N range -- rel-810 has fsupgrade-10.sh (which a 1..9 loop missed) and
    # the set grows on patch releases.
    local upgrade_scripts=( "${SCRIPT_DIR}"/upgrade/fsupgrade-*.sh )
    [[ ${#upgrade_scripts[@]} -gt 0 ]] || { echo "No fsupgrade-*.sh scripts found"; return 1; }
    for script in "${upgrade_scripts[@]}"; do
        assert_script_syntax "${script}"
    done
}

@test "upgrade scripts have priorOpenemrVersion or echo Start" {
    # At least one upgrade script should indicate version upgrade
    local found
    found=$(grep -l 'priorOpenemrVersion\|echo "Start: Upgrade' "${SCRIPT_DIR}"/upgrade/fsupgrade-*.sh 2>/dev/null | head -1)
    [[ -n "$found" ]]
}

@test "devtoolsLibrary.source exists" {
    assert_file_exists "${SCRIPT_DIR}/utilities/devtoolsLibrary.source"
}

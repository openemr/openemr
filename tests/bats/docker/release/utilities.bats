# BATS: release utilities — devtoolsLibrary, unlock_admin

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir release)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
    UTILS="${SCRIPT_DIR}/utilities"
}

@test "utilities: devtoolsLibrary.source exists and is readable" {
    assert_file_exists "${UTILS}/devtoolsLibrary.source"
}

@test "utilities: devtoolsLibrary defines prepareVariables" {
    assert_script_contains "${UTILS}/devtoolsLibrary.source" 'prepareVariables()'
}

@test "utilities: devtoolsLibrary sets CONFIGURATION" {
    assert_script_contains "${UTILS}/devtoolsLibrary.source" 'CONFIGURATION='
}

@test "utilities: devtoolsLibrary uses MYSQL_* and OE_*" {
    assert_script_contains "${UTILS}/devtoolsLibrary.source" 'MYSQL_HOST'
    assert_script_contains "${UTILS}/devtoolsLibrary.source" 'MYSQL_ROOT_PASS'
    assert_script_contains "${UTILS}/devtoolsLibrary.source" 'OE_USER'
}

@test "utilities: devtoolsLibrary setGlobalSettings" {
    assert_script_contains "${UTILS}/devtoolsLibrary.source" 'setGlobalSettings'
}

@test "utilities: unlock_admin.sh exists and runs php" {
    assert_file_exists "${UTILS}/unlock_admin.sh"
    assert_script_contains "${UTILS}/unlock_admin.sh" 'php'
    assert_script_contains "${UTILS}/unlock_admin.sh" 'unlock_admin.php'
}

@test "utilities: unlock_admin.sh cd to /root" {
    assert_script_contains "${UTILS}/unlock_admin.sh" '/root'
}

@test "utilities: unlock_admin.php exists" {
    assert_file_exists "${UTILS}/unlock_admin.php"
}

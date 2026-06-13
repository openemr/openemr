# BATS: flex utilities — devtools, unlock_admin, demo sql

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
    UTILS="${SCRIPT_DIR}/utilities"
}

@test "flex utilities: devtoolsLibrary.source" {
    assert_file_exists "${UTILS}/devtoolsLibrary.source"
    assert_script_contains "${UTILS}/devtoolsLibrary.source" 'prepareVariables()'
}

@test "flex utilities: unlock_admin.sh and unlock_admin.php" {
    assert_script_contains "${UTILS}/unlock_admin.sh" 'unlock_admin.php'
    assert_file_exists "${UTILS}/unlock_admin.php"
}

@test "flex utilities: devtools script exists" {
    assert_file_exists "${UTILS}/devtools"
}

@test "flex utilities: demo_5_0_0_5.sql exists" {
    assert_file_exists "${UTILS}/demo_5_0_0_5.sql"
}

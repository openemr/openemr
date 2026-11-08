# BATS: binary utilities

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
    UTILS="${SCRIPT_DIR}/utilities"
}

@test "binary utilities: devtoolsLibrary.source has prepareVariables" {
    assert_script_contains "${UTILS}/devtoolsLibrary.source" 'prepareVariables()'
}

@test "binary utilities: unlock_admin.sh runs php unlock_admin.php" {
    assert_script_contains "${UTILS}/unlock_admin.sh" 'unlock_admin.php'
}

@test "binary utilities: unlock_admin.php exists" {
    assert_file_exists "${UTILS}/unlock_admin.php"
}

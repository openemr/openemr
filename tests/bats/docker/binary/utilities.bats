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

@test "binary utilities: entrypoint_query.php exists" {
    assert_file_exists "${UTILS}/entrypoint_query.php"
}

@test "binary utilities: EntrypointQuery.php exists" {
    assert_file_exists "${UTILS}/EntrypointQuery.php"
}

@test "binary utilities: entrypoint query CLI matches the canonical copy" {
    local root
    root="$(get_repo_root)"
    assert_files_identical "${root}/src/Common/Docker/entrypoint_query.php" \
        "${UTILS}/entrypoint_query.php"
    assert_files_identical "${root}/src/Common/Docker/EntrypointQuery.php" \
        "${UTILS}/EntrypointQuery.php"
}

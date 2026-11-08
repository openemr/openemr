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

@test "flex Dockerfile: demo_5_0_0_5.sql fetch is wired up" {
    # The 50 MB demo SQL is no longer committed to the repo; it's fetched at
    # build time from openemr-devops pinned to a specific commit SHA, with
    # checksum verification. Verify the Dockerfile carries the right plumbing
    # so a build will actually retrieve and verify it.
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'DEMO_SQL_REPO_SHA='
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'DEMO_SQL_SHA256='
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'demo_5_0_0_5.sql'
    assert_file_contains "${SCRIPT_DIR}/Dockerfile" 'sha256sum -c -'
}

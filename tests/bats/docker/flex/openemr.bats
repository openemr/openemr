# BATS: flex openemr.sh — FLEX repo, EASY_DEV_MODE

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "flex openemr.sh: valid bash syntax" {
    assert_script_syntax "${SCRIPT_DIR}/openemr.sh"
}

@test "flex openemr.sh: FLEX_REPOSITORY and FLEX_REPOSITORY_BRANCH" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'FLEX_REPOSITORY'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'FLEX_REPOSITORY_BRANCH'
}

@test "flex openemr.sh: EASY_DEV_MODE and EASY_DEV_MODE_NEW" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'EASY_DEV_MODE'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'EASY_DEV_MODE_NEW'
}

@test "flex openemr.sh: git clone for repo fetch" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'git clone'
}

@test "flex openemr.sh: sources devtoolsLibrary.source" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'devtoolsLibrary.source'
}

@test "flex openemr.sh: wait_for_mysql and is_configured" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'wait_for_mysql()'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'is_configured()'
}

@test "flex openemr.sh: MANUAL_SETUP, K8S, SWARM_MODE" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'MANUAL_SETUP='
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'SWARM_MODE='
}

@test "flex openemr.sh: swarm follower defers leader wait for flex build" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'SWARM_WAIT_DEFERRED=yes'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'Deferring docker-leader wait until local flex build is ready'
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'wait_for_swarm_completion'
}

@test "flex openemr.sh: INSANE_DEV_MODE or DEVELOPER_TOOLS" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'EASY_DEV_MODE_NEW'
    grep -qE 'INSANE_DEV_MODE|DEVELOPER_TOOLS' "${SCRIPT_DIR}/openemr.sh"
}

@test "flex openemr.sh: default FLEX_REPOSITORY github.com/openemr" {
    assert_script_contains "${SCRIPT_DIR}/openemr.sh" 'github.com/openemr'
}

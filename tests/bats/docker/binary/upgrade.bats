# BATS: binary upgrade scripts (1–13)

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
    UPGRADE_DIR="${SCRIPT_DIR}/upgrade"
    # Bump this together with upgrade/docker-version when adding an
    # fsupgrade script. The loops below derive their bound from it, so the
    # script set and the version marker cannot drift apart silently.
    LATEST_DOCKER_VERSION=13
}

@test "binary upgrade: docker-version matches the newest fsupgrade script" {
    assert_file_exists "${UPGRADE_DIR}/docker-version"
    run cat "${UPGRADE_DIR}/docker-version"
    [[ ${output} == "${LATEST_DOCKER_VERSION}" ]]
}

@test "binary upgrade: fsupgrade-1 through docker-version exist" {
    for (( i = 1; i <= LATEST_DOCKER_VERSION; ++i )); do
        assert_file_exists "${UPGRADE_DIR}/fsupgrade-${i}.sh"
    done
}

@test "binary upgrade: each iterates sites" {
    for (( i = 1; i <= LATEST_DOCKER_VERSION; ++i )); do
        assert_script_contains "${UPGRADE_DIR}/fsupgrade-${i}.sh" 'sites/\*/'
    done
}

@test "binary upgrade: no fsupgrade past docker-version" {
    [[ ! -f "${UPGRADE_DIR}/fsupgrade-$(( LATEST_DOCKER_VERSION + 1 )).sh" ]]
}

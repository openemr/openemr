# BATS: binary upgrade scripts (1–8)

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir binary)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
    UPGRADE_DIR="${SCRIPT_DIR}/upgrade"
}

@test "binary upgrade: docker-version is 8" {
    assert_file_exists "${UPGRADE_DIR}/docker-version"
    run cat "${UPGRADE_DIR}/docker-version"
    [[ $output == "8" ]]
}

@test "binary upgrade: fsupgrade-1 through 8 exist" {
    for i in 1 2 3 4 5 6 7 8; do
        assert_file_exists "${UPGRADE_DIR}/fsupgrade-${i}.sh"
    done
}

@test "binary upgrade: each iterates sites" {
    for i in 1 2 3 4 5 6 7 8; do
        assert_script_contains "${UPGRADE_DIR}/fsupgrade-${i}.sh" 'sites/\*/'
    done
}

@test "binary upgrade: no fsupgrade-9" {
    [[ ! -f "${UPGRADE_DIR}/fsupgrade-9.sh" ]]
}

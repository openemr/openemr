# BATS: flex kcov-wrapper.sh

load '../helpers'

setup() {
    SCRIPT_DIR="$(get_script_dir flex)"
    [[ -n "$SCRIPT_DIR" ]] && [[ -d "$SCRIPT_DIR" ]]
}

@test "flex kcov-wrapper.sh: kcov and openemr.sh" {
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'kcov'
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'openemr.sh'
}

@test "flex kcov-wrapper.sh: httpd FOREGROUND" {
    assert_script_contains "${SCRIPT_DIR}/kcov-wrapper.sh" 'httpd'
}
